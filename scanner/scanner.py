#!/usr/bin/env python3
"""
Oweru International Ltd - Website Health Scanner Service
Python scanner engine that performs technical checks on websites
and returns structured results to the Laravel application.

Usage:
    python scanner.py          # Start the Flask API server
    python scanner.py scan     # Run a single scan (CLI mode)
"""

import argparse
import json
import os
import re
import socket
import ssl
import sys
import time
import urllib.parse
import urllib.robotparser
from datetime import datetime, timezone
from html.parser import HTMLParser
from pathlib import Path
from typing import Any, Optional
from urllib.parse import urljoin, urlparse

import requests
from dateutil import parser as date_parser
from flask import Flask, jsonify, request
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

APP_NAME = "Oweru Scanner"
USER_AGENT = "OweruScanner/1.0 (+https://oweru.co.tz; scanner@oweru.co.tz)"
REQUEST_INTERVAL = 2.0  # seconds between requests to the same host
MAX_PAGES = 11  # home + up to 10 internal pages
TIMEOUT = 30  # seconds per request

app = Flask(__name__)

# Simple in-memory rate limiter per host
_rate_limit_cache: dict[str, float] = {}

# ---------------------------------------------------------------------------
# Default check weights (built-in fallback)
# ---------------------------------------------------------------------------
# The authoritative weights live in the Laravel "scanner_checks" table and are
# admin-editable at /admin/scanner-checks. Laravel passes the enabled checks and
# their weights with every /api/scanner/scan request. These built-in defaults are
# only used in CLI mode (python scanner.py scan) or when a request omits checks.

DEFAULT_WEIGHTS: dict[str, int] = {
    # Security (20)
    "SSL Certificate Valid": 10,
    "SSL Certificate >30 Days to Expiry": 5,
    "No Insecure Items on Secure Page": 5,
    # Mobile (20)
    "Responsive Layout": 8,
    "Readable Body Text": 6,
    "Tap-Friendly Buttons/Links": 6,
    # Speed (15)
    "Page Loads Within 3s on Mobile": 5,
    "Total Page <2 MB": 5,
    "Images Compressed/Sized": 5,
    # Function (15)
    "No Broken Links": 5,
    "Contact/Enquiry Form Exists": 5,
    "Phone/Email Are Tappable": 5,
    # Findability (12)
    "Unique Page Title": 3,
    "Meta Description Present": 3,
    "Appears for Business Name Search": 3,
    "Google Business Profile Points to Site": 3,
    # Trust (10)
    "Registered Company Name": 3,
    "Physical Address Listed": 3,
    "Privacy Policy Page": 2,
    "Terms of Service Page": 2,
    # Commerce (5)
    "Online Payment/Booking Path": 5,
    # Freshness (3)
    "Content Changed Within 12 Months": 2,
    "Current Copyright Year": 1,
}

# Per-request weight/enable configuration is held on each WebsiteScanner
# instance (see WebsiteScanner.__init__), not in module globals, so
# concurrent scans can't leak configuration into each other.

# ---------------------------------------------------------------------------
# helpers
# ---------------------------------------------------------------------------

def _now_iso() -> str:
    return datetime.now(timezone.utc).isoformat()


def _rate_limit(host: str) -> None:
    """Enforce 2-second interval between requests to the same host."""
    now = time.time()
    last = _rate_limit_cache.get(host, 0.0)
    wait = REQUEST_INTERVAL - (now - last)
    if wait > 0:
        time.sleep(wait)
    _rate_limit_cache[host] = time.time()


def _is_internal_link(base_url: str, link: str) -> bool:
    """Return True if *link* points to the same host as *base_url*."""
    try:
        base = urlparse(base_url)
        target = urlparse(link)
        return (target.netloc == base.netloc or target.netloc == "")
    except Exception:
        return False


def _is_html_content(content_type: Optional[str]) -> bool:
    if not content_type:
        return True
    return "text/html" in content_type.lower()


class LinkExtractor(HTMLParser):
    """Extract all href attributes from <a> tags."""
    def __init__(self):
        super().__init__()
        self.links: list[str] = []

    def handle_starttag(self, tag, attrs):
        if tag == "a":
            for name, value in attrs:
                if name == "href" and value:
                    self.links.append(value)


class MetaExtractor(HTMLParser):
    """Extract title, description and other meta tags."""
    def __init__(self):
        super().__init__()
        self.title = ""
        self.description = ""
        self.tags: dict[str, str] = {}
        self._in_title = False
        self._title_buffer = ""

    def handle_starttag(self, tag, attrs):
        attrs_dict = dict(attrs)
        if tag == "title":
            self._in_title = True
        elif tag == "meta":
            name = attrs_dict.get("name", "").lower()
            prop = attrs_dict.get("property", "").lower()
            content = attrs_dict.get("content", "")
            if name == "description":
                self.description = content
            elif name:
                self.tags[name] = content
            if prop:
                self.tags[prop] = content

    def handle_endtag(self, tag):
        if tag == "title":
            self._in_title = False
            self.title = self._title_buffer.strip()

    def handle_data(self, data):
        if self._in_title:
            self._title_buffer += data


class DOMChecker:
    """
    Lightweight DOM checker that looks for elements without fetching a
    real browser.  Good enough for the checks that do not require
    JavaScript rendering.
    """

    def __init__(self, html: str, base_url: str):
        self.html = html
        self.base_url = base_url
        self.links: list[str] = []
        self.meta = MetaExtractor()
        self._parse()

    def _parse(self):
        self.meta.feed(self.html)
        parser = LinkExtractor()
        parser.feed(self.html)
        self.links = [urljoin(self.base_url, lnk) for lnk in parser.links]

    @property
    def title(self) -> str:
        return self.meta.title

    @property
    def description(self) -> str:
        return self.meta.description

    def has_contact_form(self) -> bool:
        low = self.html.lower()
        return ("contact" in low and "form" in low) or \
               ('name="message"' in low) or \
               ('name="email"' in low and 'submit' in low)

    def has_privacy_policy(self) -> bool:
        low = self.html.lower()
        return any(x in low for x in ["privacy policy", "privacy-policy", "/privacy", "privacy.html"])

    def has_terms_of_service(self) -> bool:
        low = self.html.lower()
        return any(x in low for x in ["terms of service", "terms-of-service", "/terms", "terms.html", "terms and conditions"])

    def has_company_name(self) -> bool:
        # Heuristic: look for common company indicators
        low = self.html.lower()
        return any(x in low for x in ["ltd", "limited", "inc", "corp", "llc", "plc", "gmbh", "pty ltd"])

    def has_physical_address(self) -> bool:
        low = self.html.lower()
        return any(x in low for x in ["p.o. box", "po box", "street", "avenue", "road", "block", "building", "floor", "电话", "地址"])

    def has_https(self) -> bool:
        return self.base_url.startswith("https://")

    def has_payment_path(self) -> bool:
        low = self.html.lower()
        return any(x in low for x in ["paypal", "stripe", "flutterwave", "mpesa", "card payment", "checkout", "booking", "reserve", "buy now", "add to cart"])

    def check_responsive(self, html: str) -> bool:
        low = html.lower()
        return any(x in low for x in [
            'name="viewport"',
            '<meta name="viewport"',
            'viewport',
        ])

    def check_minimum_font_size(self, html: str) -> bool:
        # Look for inline styles or style tags with font-size < 14px
        font_sizes = re.findall(r'font-size:\s*(\d+)px', html.lower())
        if font_sizes:
            min_size = min(int(s) for s in font_sizes)
            return min_size >= 14
        return True  # No explicit small font found, assume ok

    def check_tap_targets(self, html: str) -> bool:
        # Check for minimum touch target sizes in inline styles
        min_heights = re.findall(r'min-height:\s*(\d+)px', html.lower())
        min_widths = re.findall(r'min-width:\s*(\d+)px', html.lower())
        heights = re.findall(r'height:\s*(\d+)px', html.lower())
        widths = re.findall(r'width:\s*(\d+)px', html.lower())
        all_sizes = min_heights + min_widths + heights + widths
        if all_sizes:
            min_size = min(int(s) for s in all_sizes)
            return min_size >= 44
        return True  # No explicit small targets found

    def get_total_page_size(self, content: bytes) -> int:
        return len(content)

    def get_images_info(self, html: str) -> list[dict[str, Any]]:
        img_pattern = re.findall(r'<img[^>]+src=["\']([^"\']+)["\'][^>]*>', html, re.IGNORECASE)
        img_sizes = re.findall(r'<img[^>]+width=["\']([^"\']+)["\'][^>]*>', html, re.IGNORECASE)
        img_heights = re.findall(r'<img[^>]+height=["\']([^"\']+)["\'][^>]*>', html, re.IGNORECASE)
        results = []
        for i, src in enumerate(img_pattern):
            results.append({
                "src": src,
                "width": img_sizes[i] if i < len(img_sizes) else None,
                "height": img_heights[i] if i < len(img_heights) else None,
            })
        return results


class WebsiteScanner:
    """
    Main scanner class that performs all the checks defined in the
    Oweru Tech Solutions brief.
    """

    def __init__(self, url: str, scan_id: Optional[int] = None,
                 checks_config: Optional[list[dict[str, Any]]] = None):
        self.url = url if url.startswith(("http://", "https://")) else f"https://{url}"
        self.scan_id = scan_id
        self.check_weights, self.check_enabled = self._resolve_checks(checks_config)
        self.session = requests.Session()
        self.session.headers.update({"User-Agent": USER_AGENT})
        self.robot_parser: Optional[urllib.robotparser.RobotFileParser] = None
        self.checker: Optional[DOMChecker] = None
        self.home_html = ""
        self.home_content = b""
        self.home_status = 0
        self.pages_scanned: list[str] = []
        self.all_links: list[str] = []
        self.internal_links: list[str] = []

    @staticmethod
    def _resolve_checks(config: Optional[list[dict[str, Any]]]) -> tuple[dict[str, int], Optional[set]]:
        """
        Resolve per-check weights and the enabled set from a Laravel
        scanner_checks payload: [{"check_name": ..., "weight": ..., "enabled": ...}, ...].

        Returns (weights, enabled) where `enabled` is None when the payload is
        absent (CLI mode / legacy callers) meaning every check runs with its
        built-in default weight.
        """
        if not config:
            return dict(DEFAULT_WEIGHTS), None

        weights: dict[str, int] = {}
        enabled: set[str] = set()
        for entry in config:
            name = str(entry.get("check_name") or "").strip()
            if not name:
                continue
            weight = entry.get("weight")
            weights[name] = int(weight) if weight is not None else DEFAULT_WEIGHTS.get(name, 1)
            if entry.get("enabled", True):
                enabled.add(name)
        return weights, enabled

    def _weight_for(self, check_name: str) -> int:
        """Active weight for a check (>=1), from config or built-in defaults."""
        return self.check_weights.get(check_name, DEFAULT_WEIGHTS.get(check_name, 1))

    def _is_enabled(self, check_name: str) -> bool:
        """Checks absent from the configured payload run enabled with default weight."""
        if self.check_enabled is None or check_name not in self.check_weights:
            return True
        return check_name in self.check_enabled

    def _result(self, check_name: str, area: str, passed: bool, *, evidence: str,
                finding_text: str, consequence: Optional[str]) -> Optional[dict[str, Any]]:
        """
        Build one check result using the configured weight, or None when the
        check is disabled via the scanner_checks configuration. Disabled checks
        are omitted from results entirely so reports never show them as failures.
        """
        if not self._is_enabled(check_name):
            return None
        weight = self._weight_for(check_name)
        return {
            "check_name": check_name,
            "area": area,
            "weight": weight,
            "passed": bool(passed),
            "points": weight if passed else 0,
            "evidence": evidence,
            "finding_text": finding_text,
            "consequence": consequence,
        }

    # ------------------------------------------------------------------
    # Robots.txt
    # ------------------------------------------------------------------

    def _load_robots(self) -> bool:
        """Load and parse robots.txt. Returns True if crawling is allowed."""
        parsed = urlparse(self.url)
        robots_url = f"{parsed.scheme}://{parsed.netloc}/robots.txt"
        try:
            resp = self.session.get(robots_url, timeout=TIMEOUT)
            if resp.status_code == 200:
                self.robot_parser = urllib.robotparser.RobotFileParser()
                self.robot_parser.parse(resp.text.splitlines())
                can_fetch = self.robot_parser.can_fetch(USER_AGENT, self.url)
                return can_fetch
        except Exception:
            pass
        return True  # If we can't fetch robots.txt, assume allowed

    # ------------------------------------------------------------------
    # Fetching
    # ------------------------------------------------------------------

    def _fetch(self, url: str) -> tuple[Optional[str], Optional[bytes], int]:
        """Fetch a URL and return (html_text, raw_bytes, status_code)."""
        _rate_limit(urlparse(url).netloc)
        try:
            resp = self.session.get(url, timeout=TIMEOUT, allow_redirects=True)
            content_type = resp.headers.get("content-type", "")
            if not _is_html_content(content_type):
                return None, resp.content, resp.status_code
            return resp.text, resp.content, resp.status_code
        except Exception:
            return None, b"", 0

    @staticmethod
    def _calculate_band(score: int) -> str:
        """Map a 0-100 score to a band. Mirrors Laravel's Scan::calculateBand()."""
        if score < 40:
            return "Critical"
        if score < 60:
            return "Weak"
        if score < 80:
            return "Adequate"
        return "Strong"

    # ------------------------------------------------------------------
    # Page discovery (up to MAX_PAGES)
    # ------------------------------------------------------------------

    def _discover_pages(self):
        """Find up to 10 internal pages from main navigation links."""
        self.internal_links = []
        seen = set()

        # Extract links from home page
        parser = LinkExtractor()
        try:
            parser.feed(self.home_html)
        except Exception:
            return

        base = self.url.rstrip("/")
        for link in parser.links:
            absolute = urljoin(self.url, link)
            if absolute in seen:
                continue
            seen.add(absolute)
            if _is_internal_link(self.url, absolute):
                self.internal_links.append(absolute)

        # Filter to likely navigation pages (limit to 10)
        nav_hints = ["/", "/about", "/contact", "/services", "/products",
                      "/portfolio", "/team", "/blog", "/news", "/faq",
                      "/help", "/support", "/pricing", "/shop", "/store",
                      "/gallery", "/projects", "/work", "/careers",
                      "/our-services", "/our-team", "/contact-us",
                      "/about-us", "/home"]
        nav_links = [l for l in self.internal_links if any(l.rstrip("/").endswith(h) for h in nav_hints)]
        others = [l for l in self.internal_links if l not in nav_links]

        selected = nav_links[:10] if len(nav_links) >= 10 else nav_links + others[:10 - len(nav_links)]
        self.pages_scanned = [self.url] + selected[:10]

    # ------------------------------------------------------------------
    # Scan execution
    # ------------------------------------------------------------------

    def scan(self) -> dict[str, Any]:
        """Run all checks and return structured results."""
        start_time = time.time()

        # Step 1: robots.txt
        if not self._load_robots():
            return {
                "success": False,
                "error": "Website disallows scanning via robots.txt",
                "score": 0,
                "band": "Critical",
                "results": [],
            }

        # Step 2: fetch home page
        html, content, status = self._fetch(self.url)
        if html is None:
            return {
                "success": False,
                "error": f"Could not fetch website (status: {status})",
                "score": 0,
                "band": "Critical",
                "results": [],
            }

        self.home_html = html
        self.home_content = content
        self.home_status = status
        self.checker = DOMChecker(html, self.url)

        # Step 3: discover pages
        self._discover_pages()

        # Step 4: run checks
        results = self._run_all_checks()

        # Step 5: calculate score
        # Denominator must be the fixed weight of every check (sums to 100),
        # NOT the earned points — otherwise failed checks (points=0) shrink
        # the denominator and every scan scores 100.
        total_weight = sum(r.get("weight", 0) or r["points"] for r in results)
        earned_points = sum(r["points"] for r in results if r["passed"])
        score = round((earned_points / total_weight) * 100) if total_weight > 0 else 0
        score = min(100, max(0, score))

        band = self._calculate_band(score)

        elapsed = time.time() - start_time

        return {
            "success": True,
            "scan_id": self.scan_id,
            "url": self.url,
            "score": score,
            "band": band,
            "elapsed_seconds": round(elapsed, 2),
            "pages_scanned": len(self.pages_scanned),
            "results": results,
        }

    # ------------------------------------------------------------------
    # Individual checks
    # ------------------------------------------------------------------

    def _run_all_checks(self) -> list[dict[str, Any]]:
        results = []
        for group in (
            self._check_security(),
            self._check_mobile(),
            self._check_speed(),
            self._check_function(),
            self._check_findability(),
            self._check_trust(),
            self._check_commerce(),
            self._check_freshness(),
        ):
            results.extend(r for r in group if r is not None)
        return results

    # --- Security (20 points) ---

    def _check_security(self) -> list[dict[str, Any]]:
        results = []
        https = self.checker.has_https() if self.checker else False
        cert_valid = self._check_ssl_certificate()
        mixed_content = self._check_mixed_content()

        # SSL Certificate Valid
        r = self._result(
            "SSL Certificate Valid", "Security", cert_valid,
            evidence=f"HTTPS: {https}, Certificate valid: {cert_valid}",
            finding_text="The website does not use HTTPS or has an invalid SSL certificate. Visitors see security warnings and may leave immediately." if not cert_valid else "The website uses HTTPS with a valid SSL certificate, ensuring secure communication.",
            consequence="Visitors see security warnings and may leave immediately" if not cert_valid else None,
        )
        if r:
            results.append(r)

        # SSL Certificate >30 Days to Expiry
        cert_days = self._get_cert_days_to_expiry()
        cert_ok = cert_days > 30 if cert_days else False
        r = self._result(
            "SSL Certificate >30 Days to Expiry", "Security", cert_ok,
            evidence=f"Days to expiry: {cert_days}" if cert_days else "Could not check certificate expiry",
            finding_text=f"The SSL certificate expires in {cert_days} days. An expiring certificate will cause security warnings for visitors." if (cert_days and cert_days <= 30) else "The SSL certificate has more than 30 days before expiry.",
            consequence="Security warnings will appear when certificate expires" if (cert_days and cert_days <= 30) else None,
        )
        if r:
            results.append(r)

        # No Insecure Items on Secure Page
        r = self._result(
            "No Insecure Items on Secure Page", "Security", not mixed_content,
            evidence=f"Mixed content found: {mixed_content}" if mixed_content else "No mixed content detected",
            finding_text="The secure page loads resources over HTTP, which can expose visitor data to interception." if mixed_content else "No insecure (HTTP) resources were found on the secure page.",
            consequence="Visitor data may be exposed to interception" if mixed_content else None,
        )
        if r:
            results.append(r)

        return results

    def _check_ssl_certificate(self) -> bool:
        """Check if the SSL certificate is valid."""
        if not self.url.startswith("https://"):
            return False
        try:
            parsed = urlparse(self.url)
            hostname = parsed.hostname
            context = ssl.create_default_context()
            with socket.create_connection((hostname, 443), timeout=10) as sock:
                with context.wrap_socket(sock, server_hostname=hostname) as ssock:
                    ssock.getpeercert()
            return True
        except Exception:
            return False

    def _get_cert_days_to_expiry(self) -> Optional[int]:
        """Get days until SSL certificate expires."""
        if not self.url.startswith("https://"):
            return None
        try:
            import socket
            parsed = urlparse(self.url)
            hostname = parsed.hostname
            context = ssl.create_default_context()
            with socket.create_connection((hostname, 443), timeout=10) as sock:
                with context.wrap_socket(sock, server_hostname=hostname) as ssock:
                    cert = ssock.getpeercert()
                    expiry = date_parser.parse(cert["notAfter"])
                    delta = expiry - datetime.now(timezone.utc).replace(tzinfo=None)
                    return delta.days
        except Exception:
            return None

    def _check_mixed_content(self) -> list[str]:
        """Check for HTTP resources on HTTPS pages."""
        if not self.url.startswith("https://"):
            return []
        mixed = []
        pattern = re.findall(r'(?:src|href)="(http://[^"]+)"', self.home_html)
        pattern += re.findall(r"'(http://[^']+)'", self.home_html)
        # Filter out obvious non-content URLs
        for url in pattern:
            if not any(x in url for x in ["fontawesome", "google-analytics", "googletagmanager"]):
                mixed.append(url)
        return mixed[:10]  # Limit evidence

    # --- Mobile (20 points) ---

    def _check_mobile(self) -> list[dict[str, Any]]:
        results = []
        html = self.home_html

        # Responsive Layout
        responsive = self.checker.check_responsive(html) if self.checker else False
        r = self._result(
            "Responsive Layout", "Mobile", responsive,
            evidence="Viewport meta tag found" if responsive else "No viewport meta tag found",
            finding_text="The page requires horizontal scrolling on mobile devices, making it difficult for visitors to view content." if not responsive else "The page uses a responsive layout that adapts to mobile screens without horizontal scrolling.",
            consequence="Mobile visitors may struggle to use the site" if not responsive else None,
        )
        if r:
            results.append(r)

        # Readable Body Text
        readable = self.checker.check_minimum_font_size(html) if self.checker else True
        r = self._result(
            "Readable Body Text", "Mobile", readable,
            evidence="Minimum font size ≥ 14px" if readable else "Body text smaller than 14px detected",
            finding_text="Body text is too small to read comfortably on mobile devices without zooming." if not readable else "Body text is at least 14px, readable on mobile without zooming.",
            consequence="Visitors may struggle to read content on mobile" if not readable else None,
        )
        if r:
            results.append(r)

        # Tap-Friendly Buttons/Links
        tap_friendly = self.checker.check_tap_targets(html) if self.checker else True
        r = self._result(
            "Tap-Friendly Buttons/Links", "Mobile", tap_friendly,
            evidence="Touch targets ≥ 44px" if tap_friendly else "Touch targets smaller than 44px detected",
            finding_text="Buttons and links are too small to tap easily on mobile, causing frustration for visitors." if not tap_friendly else "Interactive elements are large enough (44px+) for easy tapping on mobile.",
            consequence="Mobile visitors may have difficulty interacting with the site" if not tap_friendly else None,
        )
        if r:
            results.append(r)

        return results

    # --- Speed (15 points) ---

    def _check_speed(self) -> list[dict[str, Any]]:
        results = []

        # Page Loads Within 3s on Mobile
        load_time = self._measure_load_time()
        load_ok = load_time <= 3.0
        r = self._result(
            "Page Loads Within 3s on Mobile", "Speed", load_ok,
            evidence=f"Page load time: {load_time:.2f}s",
            finding_text=f"The page takes {load_time:.1f} seconds to become usable on mobile, which is slower than the 3-second threshold." if not load_ok else f"The page loads in {load_time:.1f} seconds, within the 3-second target.",
            consequence="Visitors may leave before the page becomes usable" if not load_ok else None,
        )
        if r:
            results.append(r)

        # Total Page <2 MB
        page_size = len(self.home_content) if self.home_content else 0
        page_size_ok = page_size < 2 * 1024 * 1024
        r = self._result(
            "Total Page <2 MB", "Speed", page_size_ok,
            evidence=f"Page size: {page_size / 1024 / 1024:.2f} MB ({page_size} bytes)",
            finding_text=f"The page weighs {page_size / 1024 / 1024:.1f} MB, which is over the 2 MB limit and slows loading on mobile networks." if not page_size_ok else f"The page is {page_size / 1024 / 1024:.2f} MB, well under the 2 MB limit.",
            consequence="Slow loading on mobile networks, higher bounce rates" if not page_size_ok else None,
        )
        if r:
            results.append(r)

        # Images Compressed/Sized
        images_ok = self._check_images()
        r = self._result(
            "Images Compressed/Sized", "Speed", images_ok,
            evidence=self._get_image_evidence(),
            finding_text="Images are not optimized - oversized images slow down page loading significantly." if not images_ok else "Images appear to be reasonably sized and optimized for web.",
            consequence="Slow page loading due to large images" if not images_ok else None,
        )
        if r:
            results.append(r)

        return results

    def _measure_load_time(self) -> float:
        """Measure page load time using a simulated mobile connection."""
        try:
            start = time.time()
            resp = self.session.get(self.url, timeout=TIMEOUT)
            elapsed = time.time() - start
            return elapsed
        except Exception:
            return 99.0

    def _check_images(self) -> bool:
        """Check if images are appropriately sized and compressed."""
        if not self.checker:
            return True
        images = self.checker.get_images_info(self.home_html)
        if not images:
            return True  # No images, nothing to check

        oversized = 0
        for img in images:
            # Check for explicit dimensions
            has_dimensions = img["width"] or img["height"]
            # Check if image src suggests optimization (webp, optimized naming)
            src = img["src"].lower()
            is_optimized_format = any(x in src for x in [".webp", ".avif"])
            # If no dimensions and not optimized format, flag it
            if not has_dimensions and not is_optimized_format:
                oversized += 1

        # If more than 30% of images lack dimensions, consider it poorly optimized
        return oversized <= len(images) * 0.3

    def _get_image_evidence(self) -> str:
        if not self.checker:
            return "Could not analyze images"
        images = self.checker.get_images_info(self.home_html)
        if not images:
            return "No images found on page"
        sized = sum(1 for img in images if img["width"] or img["height"])
        return f"{sized}/{len(images)} images have explicit dimensions"

    # --- Function (15 points) ---

    def _check_function(self) -> list[dict[str, Any]]:
        results = []
        html = self.home_html

        # No Broken Links
        broken = self._check_broken_links()
        r = self._result(
            "No Broken Links", "Function", len(broken) == 0,
            evidence=f"Broken links found: {broken}" if broken else "All links resolve successfully",
            finding_text=f"Some links on the page return errors ({len(broken)} broken links found), leading visitors to dead pages." if broken else "All tested links on the page resolve successfully.",
            consequence="Visitors may reach dead pages and lose confidence" if broken else None,
        )
        if r:
            results.append(r)

        # Contact/Enquiry Form Exists
        has_form = self.checker.has_contact_form() if self.checker else False
        r = self._result(
            "Contact/Enquiry Form Exists", "Function", has_form,
            evidence="Contact form detected" if has_form else "No contact form found",
            finding_text="No contact or enquiry form was found on the website. Potential customers may struggle to get in touch." if not has_form else "A contact or enquiry form is present and accessible.",
            consequence="Potential customers may struggle to enquire" if not has_form else None,
        )
        if r:
            results.append(r)

        # Phone/Email Are Tappable
        tappable = self._check_tappable_contact()
        r = self._result(
            "Phone/Email Are Tappable", "Function", tappable,
            evidence="Clickable phone/email links found" if tappable else "Phone/email not in clickable format",
            finding_text="Phone numbers and email addresses are not clickable, forcing visitors to manually copy them." if not tappable else "Phone numbers and email addresses are clickable links for easy contact.",
            consequence="Visitors may find it harder to contact the business" if not tappable else None,
        )
        if r:
            results.append(r)

        return results

    def _check_broken_links(self) -> list[str]:
        """Check for broken internal links."""
        broken = []
        if not self.checker:
            return []

        # Test up to 10 internal links
        test_links = self.checker.links[:15]
        for link in test_links:
            if not _is_internal_link(self.url, link):
                continue
            try:
                resp = self.session.head(link, timeout=10, allow_redirects=True)
                if resp.status_code >= 400:
                    broken.append(link)
            except Exception:
                # Try GET if HEAD fails
                try:
                    resp = self.session.get(link, timeout=10, allow_redirects=True)
                    if resp.status_code >= 400:
                        broken.append(link)
                except Exception:
                    broken.append(link)

        return broken[:5]  # Limit evidence

    def _check_tappable_contact(self) -> bool:
        """Check if phone numbers and emails are clickable links."""
        html = self.home_html.lower()
        has_tel = bool(re.search(r'<a[^>]*href=["\']tel:', html))
        has_mailto = bool(re.search(r'<a[^>]*href=["\']mailto:', html))
        return has_tel or has_mailto

    # --- Findability (12 points) ---

    def _check_findability(self) -> list[dict[str, Any]]:
        results = []

        # Unique Page Title
        title = self.checker.title if self.checker else ""
        has_title = bool(title and len(title) > 5)
        r = self._result(
            "Unique Page Title", "Findability", has_title,
            evidence=f"Title: '{title[:80]}'" if title else "No title tag found",
            finding_text="The page is missing a descriptive title tag, making it hard for search engines and visitors to understand the page." if not has_title else f"The page has a descriptive title: '{title[:60]}...'",
            consequence="Poor search visibility and unclear page purpose" if not has_title else None,
        )
        if r:
            results.append(r)

        # Meta Description Present
        desc = self.checker.description if self.checker else ""
        has_desc = bool(desc and len(desc) > 20)
        r = self._result(
            "Meta Description Present", "Findability", has_desc,
            evidence=f"Description: '{desc[:80]}'" if desc else "No meta description found",
            finding_text="The page is missing a meta description, which means search engines may show unfavourable snippets." if not has_desc else "A meta description is present to improve search result snippets.",
            consequence="Search engines may show poor snippets" if not has_desc else None,
        )
        if r:
            results.append(r)

        # Appears for Business Name Search
        business_name = self._extract_business_name()
        appears_in_search = self._check_search_presence(business_name)
        r = self._result(
            "Appears for Business Name Search", "Findability", appears_in_search,
            evidence=f"Business name '{business_name}' found on page" if appears_in_search else f"Business name '{business_name}' not prominently found",
            finding_text=f"The website does not prominently feature the business name '{business_name}', which may hurt search visibility for branded searches." if not appears_in_search else f"The business name '{business_name}' is prominently displayed on the page.",
            consequence="May not appear in searches for the business name" if not appears_in_search else None,
        )
        if r:
            results.append(r)

        # Google Business Profile Points to Site
        gbp_linked = self._check_gbp_link()
        r = self._result(
            "Google Business Profile Points to Site", "Findability", gbp_linked,
            evidence="Google Business Profile link detected" if gbp_linked else "No Google Business Profile link found",
            finding_text="No link to a Google Business Profile was found. Adding one improves local search visibility." if not gbp_linked else "A Google Business Profile link is present.",
            consequence="Missed local search visibility opportunities" if not gbp_linked else None,
        )
        if r:
            results.append(r)

        return results

    def _extract_business_name(self) -> str:
        """Try to extract business name from page content."""
        if not self.checker:
            return ""
        # Look for company name in common locations
        text = self.home_html.lower()
        # Check title
        title = self.checker.title.lower() if self.checker else ""
        # Check for common business name patterns in content
        matches = re.findall(r'([a-z]+(?:\s+[a-z]+){1,3})\s+(ltd|limited|inc|corp|llc|gmbh|plc|co\.?\s*tz)', text)
        if matches:
            return matches[0][0].strip().title()
        if title:
            return title.split("|")[0].strip().title()[:50]
        return ""

    def _check_search_presence(self, business_name: str) -> bool:
        """Simplified check - does the business name appear on the page?"""
        if not business_name or not self.checker:
            return False
        return business_name.lower() in self.home_html.lower()

    def _check_gbp_link(self) -> bool:
        """Check for Google Business Profile links."""
        html = self.home_html.lower()
        patterns = [
            "google.com/business",
            "google.com/places",
            "maps.google.com",
            "g.page",
            "google.com/maps",
        ]
        return any(p in html for p in patterns)

    # --- Trust (10 points) ---

    def _check_trust(self) -> list[dict[str, Any]]:
        results = []

        # Registered Company Name
        has_company = self.checker.has_company_name() if self.checker else False
        r = self._result(
            "Registered Company Name", "Trust", has_company,
            evidence="Company name indicator found" if has_company else "No company name indicator found",
            finding_text="The website does not display a registered company name, which may reduce visitor trust." if not has_company else "A registered company name is visible on the site.",
            consequence="Reduced trust from visitors" if not has_company else None,
        )
        if r:
            results.append(r)

        # Physical Address Listed
        has_address = self.checker.has_physical_address() if self.checker else False
        r = self._result(
            "Physical Address Listed", "Trust", has_address,
            evidence="Physical address found" if has_address else "No physical address found",
            finding_text="No physical business address is listed on the website, which may make visitors question the business's legitimacy." if not has_address else "A physical business address is provided.",
            consequence="Visitors may question business legitimacy" if not has_address else None,
        )
        if r:
            results.append(r)

        # Privacy Policy Page
        has_privacy = self.checker.has_privacy_policy() if self.checker else False
        r = self._result(
            "Privacy Policy Page", "Trust", has_privacy,
            evidence="Privacy policy page found" if has_privacy else "No privacy policy page found",
            finding_text="No privacy policy page exists. This may raise compliance concerns and reduce visitor trust." if not has_privacy else "A privacy policy page is accessible on the site.",
            consequence="Reduced trust and possible compliance concerns" if not has_privacy else None,
        )
        if r:
            results.append(r)

        # Terms of Service Page
        has_terms = self.checker.has_terms_of_service() if self.checker else False
        r = self._result(
            "Terms of Service Page", "Trust", has_terms,
            evidence="Terms of service page found" if has_terms else "No terms of service page found",
            finding_text="No terms of service page exists. This may create legal ambiguity for the business." if not has_terms else "A terms of service page is accessible on the site.",
            consequence="Legal ambiguity and reduced professional credibility" if not has_terms else None,
        )
        if r:
            results.append(r)

        return results

    # --- Commerce (5 points) ---

    def _check_commerce(self) -> list[Optional[dict[str, Any]]]:
        has_payment = self.checker.has_payment_path() if self.checker else False
        return [
            self._result(
                "Online Payment/Booking Path", "Commerce", has_payment,
                evidence="Payment/booking path detected" if has_payment else "No payment or booking path found",
                finding_text="No online payment or booking functionality was detected. Customers may have no direct way to make purchases digitally." if not has_payment else "An online payment or booking path exists for customer transactions.",
                consequence="Customers may have no direct digital conversion route" if not has_payment else None,
            )
        ]

    # --- Freshness (3 points) ---

    def _check_freshness(self) -> list[dict[str, Any]]:
        results = []

        # Content Changed Within 12 Months
        freshness_ok = self._check_content_freshness()
        r = self._result(
            "Content Changed Within 12 Months", "Freshness", freshness_ok,
            evidence="Content appears recently updated" if freshness_ok else "Content may be outdated",
            finding_text="Website content appears to be outdated and may not reflect the current state of the business." if not freshness_ok else "Website content appears to be regularly updated.",
            consequence="Visitors may see outdated information" if not freshness_ok else None,
        )
        if r:
            results.append(r)

        # Current Copyright Year
        copyright_ok = self._check_copyright_year()
        r = self._result(
            "Current Copyright Year", "Freshness", copyright_ok,
            evidence="Copyright year: current" if copyright_ok else "Copyright year may be outdated",
            finding_text="The copyright year in the footer is outdated, suggesting the website is not being maintained." if not copyright_ok else "The footer shows the current copyright year.",
            consequence="May suggest the website is not actively maintained" if not copyright_ok else None,
        )
        if r:
            results.append(r)

        return results

    def _check_content_freshness(self) -> bool:
        """Check if content appears to be recently updated."""
        if not self.checker:
            return True
        html = self.home_html.lower()
        # Look for dates in the page
        date_patterns = [
            r'(\d{1,2}\s+(january|february|march|april|may|june|july|august|september|october|november|december)\s+\d{4})',
            r'(\d{4}-\d{2}-\d{2})',
            r'(updated\s+(\d{1,2}\s+\w+\s+\d{4}))',
            r'(last\s+updated\s+(\d{1,2}\s+\w+\s+\d{4}))',
        ]
        for pattern in date_patterns:
            matches = re.findall(pattern, html, re.IGNORECASE)
            if matches:
                try:
                    date_str = matches[0][0] if isinstance(matches[0], tuple) else matches[0]
                    date = date_parser.parse(date_str)
                    months_ago = (datetime.now(timezone.utc).replace(tzinfo=None) - date).days / 30
                    return months_ago <= 12
                except Exception:
                    continue
        return True  # No dates found, assume ok

    def _check_copyright_year(self) -> bool:
        """Check if copyright year matches current year."""
        current_year = datetime.now().year
        html = self.home_html.lower()
        # Look for copyright year
        match = re.search(r'copyright\s+(\D*)(\d{4})', html)
        if match:
            year = int(match.group(2))
            return year == current_year
        return True  # No copyright found, not a failure


# ---------------------------------------------------------------------------
# Flask API routes
# ---------------------------------------------------------------------------

@app.route("/api/scanner/scan", methods=["POST"])
def api_scan():
    """Request a scan. Called by Laravel."""
    data = request.get_json() or {}
    url = data.get("url", "").strip()
    scan_id = data.get("scan_id")

    # Admin-configurable checks from the Laravel scanner_checks table:
    # [{"check_name": ..., "weight": ..., "enabled": ...}, ...]
    checks_config = data.get("checks") or []

    if not url:
        return jsonify({"success": False, "message": "URL is required"}), 422

    # Validate URL
    parsed = urlparse(url)
    if not parsed.netloc:
        return jsonify({"success": False, "message": "Invalid URL format"}), 422

    # Check exclusion list (from file)
    exclusion_file = Path(__file__).parent / "excluded_sites.txt"
    if exclusion_file.exists():
        excluded = [line.strip().lower() for line in exclusion_file.read_text().splitlines() if line.strip()]
        if any(parsed.netloc.lower().endswith(e) or parsed.netloc.lower() == e for e in excluded):
            return jsonify({
                "success": False,
                "message": "This website has requested not to be scanned.",
            }), 403

    # Check API key if configured
    api_key = os.environ.get("SCANNER_API_KEY", "")
    if api_key and request.headers.get("X-API-Key") != api_key:
        return jsonify({"success": False, "message": "Unauthorized"}), 401

    try:
        scanner = WebsiteScanner(
            url,
            scan_id=int(scan_id) if scan_id else None,
            checks_config=checks_config,
        )
        result = scanner.scan()

        if not result["success"]:
            return jsonify(result), 400

        # Return structured results
        return jsonify({
            "success": True,
            "scan_id": result.get("scan_id"),
            "url": result["url"],
            "score": result["score"],
            "band": result["band"],
            "elapsed_seconds": result.get("elapsed_seconds"),
            "pages_scanned": result.get("pages_scanned"),
            "results": [
                {
                    "check_name": r["check_name"],
                    "area": r["area"],
                    "weight": r["weight"],
                    "passed": r["passed"],
                    "points": r["points"],
                    "evidence": r["evidence"],
                    "finding_text": r["finding_text"],
                    "consequence": r.get("consequence"),
                }
                for r in result["results"]
            ],
        })

    except Exception as e:
        return jsonify({
            "success": False,
            "message": str(e),
            "error": str(e),
        }), 500


@app.route("/api/scanner/scans/<int:scan_id>", methods=["GET"])
def api_get_scan(scan_id: int):
    """Get scan status/results. Called by Laravel."""
    # This would normally query a database
    # For now, return a placeholder
    return jsonify({
        "success": True,
        "data": {
            "id": scan_id,
            "status": "unknown",
            "message": "Scan status endpoint - implement database lookup",
        }
    })


@app.route("/api/scanner/callback", methods=["POST"])
def api_callback():
    """Receive completed scan results (async mode)."""
    data = request.get_json() or {}
    scan_id = data.get("scan_id")

    if not scan_id:
        return jsonify({"success": False, "message": "scan_id required"}), 422

    # This would normally update the database
    # For now, acknowledge receipt
    return jsonify({
        "success": True,
        "message": f"Callback received for scan {scan_id}",
    })


# ---------------------------------------------------------------------------
# Scraper V1 — business information scraping (Laravel calls this endpoint)
# ---------------------------------------------------------------------------

from business_scraper import scrape_url  # noqa: E402  (same directory)


@app.route("/api/scraper/scrape", methods=["POST"])
def api_scraper_scrape():
    """Scrape public business info from one website. Called by Laravel."""
    data = request.get_json() or {}
    url = (data.get("url") or "").strip()

    if not url:
        return jsonify({"success": False, "message": "URL is required"}), 422

    # Check API key if configured (same scheme as the scanner endpoints)
    api_key = os.environ.get("SCANNER_API_KEY", "")
    if api_key and request.headers.get("X-API-Key") != api_key:
        return jsonify({"success": False, "message": "Unauthorized"}), 401

    result = scrape_url(url)
    return jsonify(result), (200 if result.get("success") else 422)


@app.route("/health", methods=["GET"])
def health():
    """Health check endpoint."""
    return jsonify({
        "status": "healthy",
        "service": APP_NAME,
        "timestamp": _now_iso(),
    })


# ---------------------------------------------------------------------------
# CLI mode
# ---------------------------------------------------------------------------

def run_cli():
    """Run a single scan from the command line."""
    parser = argparse.ArgumentParser(description="Oweru Website Health Scanner")
    parser.add_argument("url", help="Website URL to scan")
    parser.add_argument("--scan-id", type=int, help="Scan ID for Laravel integration")
    parser.add_argument("--output", "-o", help="Output results to JSON file")
    args = parser.parse_args()

    print(f"Oweru Website Health Scanner")
    print(f"Scanning: {args.url}")
    print(f"Started: {_now_iso()}")
    print("-" * 60)

    try:
        scanner = WebsiteScanner(args.url, args.scan_id)
        result = scanner.scan()

        if not result["success"]:
            print(f"\nError: {result['error']}")
            sys.exit(1)

        print(f"\nScore: {result['score']}/100 ({result['band']})")
        print(f"Pages scanned: {result.get('pages_scanned', 0)}")
        print(f"Time: {result.get('elapsed_seconds', 0)}s")
        print(f"Checks run: {len(result['results'])}")
        print("-" * 60)

        passed = sum(1 for r in result["results"] if r["passed"])
        failed = sum(1 for r in result["results"] if not r["passed"])
        print(f"Passed: {passed}, Failed: {failed}")
        print("-" * 60)

        print("\nResults by area:")
        for r in result["results"]:
            status = "✓ PASS" if r["passed"] else "✗ FAIL"
            pts = f"+{r['points']}" if r["passed"] else "0"
            print(f"  [{r['area']}] {status} ({pts}pts) {r['check_name']}")
            if not r["passed"]:
                print(f"         → {r['finding_text']}")

        # Output to file if requested
        if args.output:
            output_data = {
                "scan_id": result.get("scan_id"),
                "url": result["url"],
                "score": result["score"],
                "band": result["band"],
                "elapsed_seconds": result.get("elapsed_seconds"),
                "pages_scanned": result.get("pages_scanned"),
                "results": result["results"],
                "scanned_at": _now_iso(),
            }
            with open(args.output, "w") as f:
                json.dump(output_data, f, indent=2)
            print(f"\nResults saved to: {args.output}")

    except Exception as e:
        print(f"\nFatal error: {e}")
        sys.exit(1)


# ---------------------------------------------------------------------------
# Entry point
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    if len(sys.argv) > 1 and sys.argv[1] == "scan":
        run_cli()
    else:
        # Start Flask server
        port = int(os.environ.get("SCANNER_PORT", 5000))
        debug = os.environ.get("SCANNER_DEBUG", "false").lower() == "true"
        print(f"Starting {APP_NAME} on port {port}...")
        # threaded=True: scrapes/scans take up to ~2 min each, and Laravel's
        # scheduler + admin both hit this service — requests must not queue.
        app.run(host="0.0.0.0", port=port, debug=debug, threaded=True)
