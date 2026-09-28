#!/usr/bin/env python3
"""
Oweru International Ltd - Business Scraper Engine (Scraper V1)

Extracts public business information from a single permitted public website
and returns it as structured JSON. Laravel calls this via
POST /api/scraper/scrape and stores the result in the scraped_businesses table.

Reviews & ratings (Scraper V2): testimonials, star ratings and review text
are extracted ONLY from the target's own public pages (schema.org JSON-LD,
microdata, and on-page testimonial sections). No third-party review
platforms are contacted.

Responsible-scraping rules (from the Oweru build brief):
  - public pages only, no logins / private areas
  - robots.txt respected; crawling stops when disallowed
  - honest User-Agent with contact address
  - max one request per 2 seconds to the same host
  - page text is used transiently to build the structured result; no raw
    page content is stored

Usage:
    python business_scraper.py <url>     # CLI mode, prints JSON
    (Flask route is registered when imported by scanner.py)
"""

import html as html_lib
import json
import re
import threading
import time
import urllib.parse
import urllib.robotparser
from html.parser import HTMLParser
from typing import Any, Optional

import requests

APP_NAME = "Oweru Business Scraper"
USER_AGENT = "OweruScraper/1.0 (+https://oweru.co.tz; scraper@oweru.co.tz)"
REQUEST_INTERVAL = 2.0  # seconds between requests to the same host
TIMEOUT = 30  # seconds per request
MAX_PAGES = 4  # home + about/contact/services pages

# nav_hints used to find pages likely to contain business details
ABOUT_HINTS = ["about", "who-we-are", "our-story", "company", "team"]
CONTACT_HINTS = ["contact", "get-in-touch", "reach-us", "connect"]
SERVICES_HINTS = ["service", "product", "solution", "what-we-do", "offer"]

# --- Directory harvesting (auto-discovery) ----------------------------------
#
# A page is treated as a business DIRECTORY when it links out to many
# DISTINCT external domains (typical of listing pages). Its outbound
# business links are reported as `discovered_links` so Laravel can queue
# them as new scrape targets. A normal business site links mostly to its
# own pages and social media, so it never reaches the threshold and
# discovery stays silent for it.

DIRECTORY_MIN_DISTINCT_DOMAINS = 5  # distinct external domains needed
DIRECTORY_MIN_EXTERNAL_LINKS = 8    # total external links needed
MAX_DISCOVERED_LINKS = 50           # hard cap reported per directory page

# --- Profile-page discovery (modern / SPA directories) -----------------------
#
# Modern directories (e.g. tanzapages.com) render as JavaScript apps: their
# listing pages contain almost no server-side links to EXTERNAL business
# websites, so the distinct-domain rule above never fires. Instead they
# expose businesses through INTERNAL PROFILE pages (with href /company/{id}/{name},
# /business/{id}, /listing/{slug} ...). Each profile typically links to the
# company's real external website, which is the lead we actually want.
#
# Strategy: detect profile URLs, fetch a small sample of them (rate-limited),
# and pull the first non-social external homepage from each profile page.
# The sample stays well inside the per-host rate limit and page budget.

PROFILE_PATH_PATTERN = re.compile(
    r"^/(?:company|business|businesses|listing|listings|profile|profiles|supplier|vendors?)"
    r"(?:/[^/]{1,80}){1,3}$",
    re.IGNORECASE,
)
PROFILE_MIN_LINKS = 4    # distinct profile URLs before we treat the page as a directory
PROFILE_SAMPLE_LIMIT = 5 # profile pages fetched per scrape (rate-limited, ~10s)

# Social platforms, portals and asset hosts that are never business leads.
# Matched as full domains: host equals the entry, or is a subdomain of it
# (e.g. www.facebook.com matches facebook.com) — so real domains like
# market.co.tz or box.com are never caught by short patterns.
NON_BUSINESS_DOMAINS = (
    "facebook.com", "fb.com", "instagram.com", "twitter.com", "x.com",
    "linkedin.com", "youtube.com", "youtu.be", "tiktok.com", "whatsapp.com",
    "wa.me", "telegram.org", "t.me", "pinterest.com", "reddit.com",
    "snapchat.com", "vimeo.com", "flickr.com", "medium.com", "apple.com",
    "microsoft.com", "yahoo.com", "bing.com", "amazon.com", "wikipedia.org",
    "w3.org", "schema.org", "cloudflare.com", "gravatar.com", "jsdelivr.net",
    "cdnjs.cloudflare.com", "bit.ly", "gmail.com", "hotmail.com",
    "outlook.com", "canva.com", "mailchimp.com", "google.com",
    "googleapis.com", "gstatic.com", "goo.gl", "googletagmanager.com",
    "google-analytics.com", "doubleclick.net", "wp.com", "wixstatic.com",
    "github.com", "gitlab.com", "amazonaws.com", "azurewebsites.net",
    "herokuapp.com", "netlify.app", "vercel.app", "pages.dev", "web.app",
    "firebaseapp.com",
)

# Links pointing straight at documents/assets are never business sites
NON_BUSINESS_EXTENSIONS = (
    ".jpg", ".jpeg", ".png", ".gif", ".webp", ".svg", ".pdf", ".doc",
    ".docx", ".xls", ".xlsx", ".ppt", ".zip", ".rar", ".mp4", ".mp3",
    ".css", ".js", ".ico", ".woff", ".woff2", ".ttf", ".xml", ".json",
)

_last_request_at: dict[str, float] = {}


# --- Headless-browser rendering (JavaScript-heavy sites) ---------------------
#
# Some sites ship an empty HTML shell and build the page with JavaScript
# (React/Vue/Nuxt/Next...). Plain HTTP scraping sees almost no text, so
# services/descriptions/contacts come back empty. When a page looks like a
# JS shell we re-fetch it through headless Chromium (Playwright — the Build
# Brief's optional browser-automation tool) and use the RENDERED HTML.
#
# Playwright is optional: if the package or browser is missing, rendering
# silently no-ops and the HTTP fetch is used as-is.


class HeadlessRenderer:
    """Lazy Playwright wrapper — one browser per engine process.

    Flask serves requests threaded=True, but Playwright's sync API is not
    thread-safe, so every render is serialized through a lock.
    """

    _lock = threading.Lock()

    def __init__(self) -> None:
        self._pw: Any = None
        self._browser: Any = None
        self.available: Optional[bool] = None  # None = not tried yet

    def _ensure(self) -> bool:
        if self.available is True:
            return True
        if self.available is False:
            return False
        try:
            from playwright.sync_api import sync_playwright  # optional dep

            self._pw = sync_playwright().start()
            self._browser = self._pw.chromium.launch(headless=True)
            self.available = True
        except Exception:
            self.available = False
        return self.available

    def render(self, url: str, wait_ms: int = 3500) -> Optional[str]:
        """Return the JS-rendered HTML of a page, or None."""
        if not self._ensure():
            return None
        try:
            with self._lock:
                page = self._browser.new_page(user_agent=USER_AGENT)
                page.goto(url, timeout=25000, wait_until="domcontentloaded")
                # give the SPA time to hydrate and fetch its data
                page.wait_for_timeout(wait_ms)
                html = page.content()
                page.close()
            return html if html and len(html) > 500 else None
        except Exception:
            return None

    def close(self) -> None:
        try:
            if self._browser:
                self._browser.close()
            if self._pw:
                self._pw.stop()
        except Exception:
            pass


_renderer = HeadlessRenderer()


_SPA_MARKERS = (
    "__next", "__nuxt", "ng-version", "ng-app", "data-reactroot",
    "react", "vue", "svelte", "window.__", "/bundle.", "/app.", "/chunk.",
)


def _has_spa_markers(html: str) -> bool:
    low = html.lower()
    return "<script" in low and any(m in low for m in _SPA_MARKERS)


def _looks_like_js_shell(html: str) -> bool:
    """Heuristic: server-rendered shell with little text and an SPA footprint."""
    text = _strip_tags(html).strip()
    if len(text) >= 400:
        return False  # plenty of server-rendered text — no rendering needed
    if _has_spa_markers(html):
        return True
    low = html.lower()
    return bool(
        re.search(r'<div[^>]+id=["\'](?:root|app|__next|__nuxt|app-root)["\']', low)
    )


def _rate_limit(host: str) -> None:
    """Enforce 2-second interval between requests to the same host."""
    now = time.time()
    last = _last_request_at.get(host, 0.0)
    wait = REQUEST_INTERVAL - (now - last)
    if wait > 0:
        time.sleep(wait)
    _last_request_at[host] = time.time()


def _normalize_url(raw: str) -> Optional[str]:
    """Validate and normalize a public URL. Returns None when invalid."""
    raw = (raw or "").strip()
    if not raw:
        return None
    if not raw.startswith(("http://", "https://")):
        raw = f"https://{raw}"
    parsed = urllib.parse.urlparse(raw)
    if not parsed.netloc or "." not in parsed.netloc:
        return None
    # Block private/local addresses — public business sites only
    host = parsed.netloc.lower().split(":")[0]
    if host in ("localhost", "127.0.0.1", "0.0.0.0") or host.endswith((".local", ".internal")):
        return None
    if re.match(r"^10\.|^192\.168\.|^172\.(1[6-9]|2\d|3[01])\.", host):
        return None
    return raw


def _is_non_business_host(host: str) -> bool:
    """True when the host is a social platform, portal or asset provider."""
    return any(
        host == bad or host.endswith("." + bad)
        for bad in NON_BUSINESS_DOMAINS
    )


class TextExtractor(HTMLParser):
    """Collect visible text (space-joined AND line-preserving) plus links."""

    SKIP_TAGS = {"script", "style", "noscript", "template"}
    # Block-level elements end a text line. Without tracking these, the whole
    # page collapses to ONE giant line and the line-anchored services/about/
    # address regexes can never match.
    BLOCK_TAGS = {
        "p", "div", "section", "article", "header", "footer", "main",
        "aside", "nav", "h1", "h2", "h3", "h4", "h5", "h6", "li", "ul",
        "ol", "tr", "td", "th", "table", "br", "hr", "blockquote",
        "figure", "figcaption", "dt", "dd", "form", "label", "address",
    }

    def __init__(self) -> None:
        super().__init__(convert_charrefs=True)
        self.text_parts: list[str] = []
        self.lines: list[str] = []
        self._line_buf: list[str] = []
        self._pending_break = False
        self.links: list[tuple[str, str]] = []  # (href, anchor text)
        self._skip_depth = 0
        self._link_href: Optional[str] = None
        self._link_text: list[str] = []

    def _flush_line(self) -> None:
        line = " ".join(self._line_buf).strip()
        if line:
            self.lines.append(line)
        self._line_buf = []
        self._pending_break = False

    def handle_starttag(self, tag: str, attrs: list[tuple[str, str]]) -> None:
        attrs_dict = dict(attrs)
        if tag in self.SKIP_TAGS:
            self._skip_depth += 1
            return
        if tag in self.BLOCK_TAGS:
            self._pending_break = True
        if tag == "a":
            href = attrs_dict.get("href", "")
            self._link_href = href if href else None
            self._link_text = []

    def handle_endtag(self, tag: str) -> None:
        if tag in self.SKIP_TAGS and self._skip_depth > 0:
            self._skip_depth -= 1
            return
        if tag in self.BLOCK_TAGS:
            self._pending_break = True
        if tag == "a" and self._link_href is not None:
            self._flush_line()
            text = " ".join(self._link_text).split()
            text = " ".join(text)
            self.links.append((self._link_href, text))
            self._link_href = None
            self._link_text = []

    def handle_data(self, data: str) -> None:
        if self._skip_depth > 0:
            return
        cleaned = " ".join(data.split())
        if not cleaned:
            return
        if self._pending_break:
            self._flush_line()
        self.text_parts.append(cleaned)
        self._line_buf.append(cleaned)
        if self._link_href is not None:
            self._link_text.append(cleaned)


def _strip_tags(html: str) -> str:
    extractor = TextExtractor()
    try:
        extractor.feed(html)
    except Exception:
        pass
    return " ".join(extractor.text_parts)


def _strip_tags_lines(html: str) -> str:
    """Visible text with each block element on its own line. Field
    extraction (services/about/address) is line-anchored, so the page
    structure must survive tag stripping."""
    extractor = TextExtractor()
    try:
        extractor.feed(html)
    except Exception:
        pass
    extractor._flush_line()
    return "\n".join(extractor.lines)


class ReviewExtractor:
    """
    Extract on-site reviews, testimonials and star ratings from a page.

    Sources (all from the target's own public pages only):
      1. schema.org JSON-LD blocks (Review / AggregateRating inside any @type)
      2. schema.org microdata attributes (itemprop="review" / "aggregateRating")
      3. on-page testimonial sections (blockquote / figure / testimonial class names)

    Returns a dict: {reviews: [...], rating_avg, rating_count, rating_source}
    """

    MAX_REVIEWS = 5
    MAX_REVIEW_CHARS = 400

    def __init__(self) -> None:
        self.reviews: list[dict[str, Any]] = []
        self._seen: set[str] = set()

    # ------------------------------------------------------------------
    # JSON-LD (schema.org)
    # ------------------------------------------------------------------

    def _parse_jsonld(self, html: str) -> None:
        for match in re.finditer(
            r'<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>',
            html, re.IGNORECASE | re.DOTALL,
        ):
            raw = match.group(1).strip()
            if not raw:
                continue
            try:
                data = json.loads(raw)
            except Exception:
                continue
            self._walk_jsonld(data, depth=0)

    def _walk_jsonld(self, node: Any, depth: int) -> None:
        if depth > 6:
            return
        if isinstance(node, list):
            for item in node[:30]:
                self._walk_jsonld(item, depth + 1)
            return
        if not isinstance(node, dict):
            return

        node_type = node.get("@type", "")
        types = node_type if isinstance(node_type, list) else [node_type]

        if any(t in ("Review", "UserReview", "ProductReview") for t in types):
            self._add_review(
                text=node.get("reviewBody", "") or node.get("description", ""),
                author=self._author_name(node.get("author")),
                rating=self._rating_value(node.get("reviewRating")),
                source="schema.org",
            )

        # AggregateRating may sit on Product/LocalBusiness/Organization/etc.
        agg = node.get("aggregateRating")
        if isinstance(agg, dict):
            self._aggregate = {
                "rating": self._rating_value(agg),
                "count": agg.get("ratingCount") or agg.get("reviewCount") or agg.get("reviewCount") or 0,
            }

        for key in ("review", "reviews", "itemReviewed"):
            child = node.get(key)
            if child:
                self._walk_jsonld(child, depth + 1)

    @staticmethod
    def _author_name(author: Any) -> str:
        if isinstance(author, dict):
            return str(author.get("name", "") or "").strip()
        if isinstance(author, str):
            return author.strip()
        return ""

    @staticmethod
    def _rating_value(rating: Any) -> Optional[float]:
        if isinstance(rating, dict):
            rating = rating.get("ratingValue")
        try:
            value = float(rating)
            return value if 0 < value <= 5 else None
        except (TypeError, ValueError):
            return None

    # ------------------------------------------------------------------
    # Microdata (itemprop attributes)
    # ------------------------------------------------------------------

    def _parse_microdata(self, html: str) -> None:
        for match in re.finditer(
            r'itemprop=["\']reviewBody["\'][^>]*>(.*?)<', html, re.IGNORECASE | re.DOTALL
        ):
            text = " ".join(html_lib.unescape(match.group(1)).split())
            self._add_review(text=text, author="", rating=None, source="microdata")

        for match in re.finditer(
            r'itemprop=["\']ratingValue["\'][^>]*content=["\']([0-9.]+)["\']',
            html, re.IGNORECASE,
        ):
            try:
                value = float(match.group(1))
                if 0 < value <= 5:
                    self._aggregate = self._aggregate or {"rating": value, "count": 0}
            except ValueError:
                pass

    # ------------------------------------------------------------------
    # On-page testimonials
    # ------------------------------------------------------------------

    TESTIMONIAL_HINTS = (
        "testimonial", "review", "what-they-say", "what-our", "client-say",
        "feedback", "quote",
    )

    def _parse_testimonials(self, html: str, texts: str) -> None:
        # blockquote / <figure class=...quote...> blocks are the usual markup
        blocks = re.findall(
            r'<(?:blockquote|figure)[^>]*>(.*?)</(?:blockquote|figure)>',
            html, re.IGNORECASE | re.DOTALL,
        )

        page_has_section = any(hint in html.lower() for hint in self.TESTIMONIAL_HINTS)

        for block in blocks:
            block_text = " ".join(html_lib.unescape(re.sub(r"<[^>]+>", " ", block)).split())
            if not (25 <= len(block_text) <= self.MAX_REVIEW_CHARS):
                continue

            looks_like_review = (
                page_has_section
                or block_text.count("\"") >= 1
                or block_text.lower().startswith(("\"", "“", "'"))
            )
            if not looks_like_review:
                continue

            self._add_review(text=block_text, author="", rating=None, source="on-page")

        # Star ratings rendered as text, e.g. "4.8 out of 5" / "rated 4.5/5"
        if not self._aggregate:
            star = re.search(
                r'(?:rated|rating(?: of)?|score)\s*(?:of)?\s*([0-5](?:\.\d)?)\s*(?:/|out of)\s*5',
                texts, re.IGNORECASE,
            )
            if star:
                try:
                    value = float(star.group(1))
                    if 0 < value <= 5:
                        self._aggregate = {"rating": value, "count": 0}
                except ValueError:
                    pass

    # ------------------------------------------------------------------
    # Helpers
    # ------------------------------------------------------------------

    def _add_review(self, text: str, author: str, rating: Optional[float], source: str) -> None:
        text = " ".join((text or "").split())[: self.MAX_REVIEW_CHARS]
        if len(text) < 25 or text in self._seen:
            return
        self._seen.add(text)
        if len(self.reviews) < self.MAX_REVIEWS:
            self.reviews.append({
                "author": author[:80],
                "text": text,
                "rating": rating,
                "source": source,
            })

    # ------------------------------------------------------------------
    # Structured business data (schema.org JSON-LD on the home/about pages)
    # ------------------------------------------------------------------

    def extract_business_jsonld(self, html_pages: list[str]) -> dict[str, Any]:
        """
        Pull Organization/LocalBusiness fields from schema.org JSON-LD.
        These are far more reliable than regex over page text, and most
        modern sites ship at least an Organization block.
        """
        fields: dict[str, Any] = {
            "business_name": "",
            "email": "",
            "phone": "",
            "address": "",
            "services": "",
            "about": "",
            "service_nodes": [],
        }

        for page_html in html_pages:
            for match in re.finditer(
                r'<script[^>]*type=["\']application/ld\+json["\'][^>]*>(.*?)</script>',
                page_html, re.IGNORECASE | re.DOTALL,
            ):
                raw = match.group(1).strip()
                if not raw:
                    continue
                try:
                    data = json.loads(raw)
                except Exception:
                    continue
                self._fill_business_fields(data, fields, depth=0)

                # Stop after we have the essentials
                if fields["business_name"] and (fields["email"] or fields["phone"]):
                    break

        return fields

    def _fill_business_fields(self, node: Any, fields: dict[str, Any], depth: int) -> None:
        if depth > 6:
            return
        if isinstance(node, list):
            for item in node[:30]:
                self._fill_business_fields(item, fields, depth + 1)
            return
        if not isinstance(node, dict):
            return

        node_type = node.get("@type", "")
        types = node_type if isinstance(node_type, list) else [node_type]

        # Service/Offer nodes anywhere in the graph name what the business sells
        if any(t in ("Service", "Offer") for t in types):
            svc_name = str(node.get("name", "") or "").strip()
            if svc_name and "service_nodes" in fields and svc_name not in fields["service_nodes"]:
                fields["service_nodes"].append(svc_name)

        business_types = {
            "Organization", "LocalBusiness", "ProfessionalService", "Corporation",
            "Store", "Restaurant", "Hotel", "Hospital", "School", "NGO",
            "EducationalOrganization", "MedicalOrganization", "HomeAndConstructionBusiness",
        }

        if any(t in business_types for t in types):
            name = str(node.get("name", "") or "").strip()
            if name and not fields["business_name"]:
                fields["business_name"] = name[:120]

            contact = node.get("contactPoint")
            if isinstance(contact, list):
                contact = contact[0] if contact else None
            if isinstance(contact, dict):
                if not fields["email"]:
                    fields["email"] = str(contact.get("email", "") or "").strip()[:200]
                if not fields["phone"]:
                    fields["phone"] = str(contact.get("telephone", "") or "").strip()[:50]

            if not fields["email"] and isinstance(node.get("email"), str):
                fields["email"] = node["email"].strip()[:200]
            if not fields["phone"] and isinstance(node.get("telephone"), str):
                fields["phone"] = node["telephone"].strip()[:50]

            addr = node.get("address")
            if isinstance(addr, list):
                addr = addr[0] if addr else None
            if isinstance(addr, dict):
                parts = [
                    str(addr.get(k, "") or "").strip()
                    for k in ("streetAddress", "addressLocality", "addressRegion", "postalCode", "addressCountry")
                ]
                joined = ", ".join(p for p in parts if p)
                if joined and not fields["address"]:
                    fields["address"] = joined[:200]
            elif isinstance(addr, str) and addr.strip() and not fields["address"]:
                fields["address"] = " ".join(addr.split())[:200]

            description = str(node.get("description", "") or "").strip()
            if description and not fields["about"]:
                fields["about"] = description[:500]

            # hasOfferCatalog / makesOffer / hasMerchantReturnPolicy often list services
            if not fields["services"]:
                service_names: list[str] = []
                catalog = node.get("hasOfferCatalog")
                if isinstance(catalog, dict):
                    catalog = catalog.get("itemListElement")
                if isinstance(catalog, list):
                    for entry in catalog[:15]:
                        item = entry.get("itemOffered", entry) if isinstance(entry, dict) else None
                        offer_name = str((item or {}).get("name", "") or "").strip() if isinstance(item, dict) else ""
                        if offer_name:
                            service_names.append(offer_name)
                makes = node.get("makesOffer")
                if isinstance(makes, list):
                    for offer in makes[:15]:
                        item = offer.get("itemOffered", {}) if isinstance(offer, dict) else {}
                        offer_name = str(item.get("name", "") or "").strip() if isinstance(item, dict) else ""
                        if offer_name:
                            service_names.append(offer_name)
                if service_names:
                    fields["services"] = ", ".join(service_names)[:400]

        # After the loop above ran on THIS node only, promote collected
        # Service/Offer names when the business node itself has no catalog.
        for key in ("subOrganization", "department", "parentOrganization", "mainEntity"):
            child = node.get(key)
            if child:
                self._fill_business_fields(child, fields, depth + 1)

    def extract(self, html_pages: list[str], all_text: str) -> dict[str, Any]:
        self._aggregate: dict[str, Any] = {"rating": None, "count": 0}

        for page_html in html_pages:
            self._parse_jsonld(page_html)
            self._parse_microdata(page_html)
        self._parse_testimonials(html_pages[0] if html_pages else "", all_text)

        return {
            "reviews": self.reviews,
            "rating_avg": self._aggregate.get("rating"),
            "rating_count": self._aggregate.get("count") or len(self.reviews) or None,
            "rating_source": (
                "schema.org" if self._aggregate.get("rating") is not None and self.reviews
                else ("on-page" if self.reviews else None)
            ),
        }


# --- Preliminary gap detection ------------------------------------------------
#
# Before any full health scan runs, staff want a fast answer to "is this
# business worth contacting?". The scrape itself already reveals the most
# glaring digital-presence gaps: no HTTPS, no mobile viewport, no way to be
# contacted, no way to pay, no evidence of care (copyright year). We surface
# those immediately from the home page we already fetched — zero extra
# requests, no rate-limit impact.
#
# IMPORTANT: check_name values REUSE the health scanner's names so Laravel's
# recommendations mapping (check_name -> Oweru service) attaches the exact
# service to sell to each gap with no new configuration. This is a quick
# triage, NOT a health scan — a low-gap-count site may still have deep
# problems only the scanner finds.

class GapDetector:
    """Detect preliminary website gaps/opportunities from a scraped home page."""

    # Gap found -> (check_name matching the scanner, plain-English opportunity)
    @staticmethod
    def detect(home_html: str, texts: str, https_ok: bool, emails: list,
               phones: list, all_html: str = "") -> list[dict[str, Any]]:
        low = home_html.lower()
        # Signals like a privacy-policy link, payment providers or tel:/mailto:
        # links often sit in the footer of ANY page (or a dedicated page) —
        # check the combined raw HTML of every fetched page, not just home.
        low_all = (all_html or home_html).lower()

        def _has_meta_description(html: str) -> bool:
            # name/content attribute order varies between sites — check each
            # <meta> tag for both attributes independently.
            for tag in re.findall(r"<meta\b[^>]*>", html, re.IGNORECASE):
                tag_l = tag.lower()
                if (re.search(r"name\s*=\s*[\"']description[\"']", tag_l)
                        and re.search(r"content\s*=\s*[\"'][^\"']{20,}[\"']", tag_l)):
                    return True
            return False

        gaps: list[dict[str, Any]] = []

        def add(check_name: str, gap: str, opportunity: str) -> None:
            gaps.append({"check_name": check_name, "gap": gap, "opportunity": opportunity})

        # Security: plain HTTP
        if not https_ok:
            add("SSL Certificate Valid",
                "No HTTPS — the site does not use a secure connection",
                "Security upgrade + trust building (SSL, security headers)")

        # Mobile: no responsive viewport
        if "viewport" not in low:
            add("Responsive Layout",
                "Not mobile-friendly — no responsive viewport meta tag",
                "Mobile-responsive redesign")

        # Contact: no enquiry form and no tappable contact links (any page)
        has_form = bool(re.search(r"<form\b", low_all)) and bool(
            re.search(r"name=[\"'](email|message|subject)[\"']|contact|enquir|inquir", low_all)
        )
        has_tel = bool(re.search(r'<a[^>]*href=["\']tel:', low_all))
        has_mailto = bool(re.search(r'<a[^>]*href=["\']mailto:', low_all))
        if not has_form and not has_tel and not has_mailto:
            add("Contact/Enquiry Form Exists",
                "No online enquiry path — no contact form or clickable contact links",
                "Enquiry form + conversion-focused contact setup")

        # Findability: no page title / no meta description
        if not re.search(r"<title[^>]*>[^<]{5,}", home_html, re.IGNORECASE):
            add("Unique Page Title",
                "Missing page title — poor search visibility",
                "SEO foundations (titles, descriptions, local search)")
        if not _has_meta_description(home_html):
            add("Meta Description Present",
                "No meta description — search results show uncontrolled snippets",
                "SEO foundations (titles, descriptions, local search)")

        # Trust: no privacy policy / no physical address anywhere in scraped text
        if not any(x in low_all for x in ("privacy policy", "privacy-policy", "/privacy", "privacy.html")):
            add("Privacy Policy Page",
                "No privacy policy — trust and compliance gap",
                "Trust pack (policies, company registration display)")
        if not re.search(r"p\.?\s*o\.?\s*box|street|avenue|road|building|floor", texts, re.IGNORECASE):
            add("Physical Address Listed",
                "No physical address visible — visitors may doubt legitimacy",
                "Trust pack (policies, company registration display)")

        # Commerce: no payment/booking path
        if not any(x in low_all for x in (
            "paypal", "stripe", "flutterwave", "mpesa", "card payment",
            "checkout", "booking", "reserve", "buy now", "add to cart",
        )):
            add("Online Payment/Booking Path",
                "No online payment or booking path — no direct digital sales route",
                "E-commerce / booking integration")

        # Freshness: stale copyright year is a cheap neglect signal
        current_year = time.gmtime().tm_year
        copy_match = re.search(r"copyright\s+\D*(\d{4})", low)
        if copy_match and int(copy_match.group(1)) < current_year:
            add("Current Copyright Year",
                f"Outdated copyright year ({copy_match.group(1)}) — site looks unmaintained",
                "Website maintenance plan")

        return gaps


class BusinessScraper:
    """Scrape public business information from one website."""

    def __init__(self, url: str) -> None:
        self.url = url
        self.host = urllib.parse.urlparse(url).netloc
        self.session = requests.Session()
        self.session.headers.update({"User-Agent": USER_AGENT, "Accept-Language": "en"})
        self.robot_parser: Optional[urllib.robotparser.RobotFileParser] = None
        self.pages: dict[str, str] = {}  # url -> stripped text
        self.raw_pages: dict[str, str] = {}  # url -> raw HTML (kept transiently for review extraction)
        self.render_mode = "http"  # "http" | "headless-browser"
        self._js_rendered = False  # home page needed the browser → render subpages too

    # ------------------------------------------------------------------
    # robots.txt
    # ------------------------------------------------------------------

    def robots_allows(self, url: str) -> bool:
        parsed = urllib.parse.urlparse(url)
        robots_url = f"{parsed.scheme}://{parsed.netloc}/robots.txt"
        if self.robot_parser is None:
            try:
                resp = self.session.get(robots_url, timeout=10)
                self.robot_parser = urllib.robotparser.RobotFileParser()
                if resp.status_code == 200:
                    self.robot_parser.parse(resp.text.splitlines())
                else:
                    # No reachable robots.txt -> assume allowed
                    return True
            except Exception:
                return True
        return self.robot_parser.can_fetch(USER_AGENT, url)

    # ------------------------------------------------------------------
    # Fetching
    # ------------------------------------------------------------------

    def fetch(self, url: str) -> Optional[str]:
        """Fetch stripped text from a URL, or None when not fetchable/allowed."""
        if not self.robots_allows(url):
            return None
        _rate_limit(self.host)
        try:
            resp = self.session.get(url, timeout=TIMEOUT, allow_redirects=True)
            if resp.status_code != 200:
                return None
            if "text/html" not in resp.headers.get("content-type", "html").lower():
                return None
            html = resp.text
            # SPA subpages are shells too when the home page needed the browser
            if self._js_rendered and _looks_like_js_shell(html):
                rendered = _renderer.render(url)
                if rendered:
                    html = rendered
            self.raw_pages[url] = html
            return _strip_tags_lines(html)
        except Exception:
            return None

    # ------------------------------------------------------------------
    # Page discovery
    # ------------------------------------------------------------------

    def discover_pages(self, home_html: str) -> None:
        """Find about/contact/services pages from home page links."""
        extractor = TextExtractor()
        try:
            extractor.feed(home_html)
        except Exception:
            return

        candidates: dict[str, str] = {}  # url -> category
        for href, _anchor in extractor.links:
            if not href or href.startswith(("mailto:", "tel:", "javascript:", "#")):
                continue
            absolute = urllib.parse.urljoin(self.url, href)
            parsed = urllib.parse.urlparse(absolute)
            if parsed.netloc != self.host or parsed.path in ("", "/"):
                continue
            path = parsed.path.lower().rstrip("/")
            for category, hints in (
                ("about", ABOUT_HINTS),
                ("contact", CONTACT_HINTS),
                ("services", SERVICES_HINTS),
            ):
                if any(hint in path for hint in hints):
                    candidates.setdefault(absolute, category)

        # At most 3 extra pages, one per category
        chosen: list[str] = []
        for category in ("about", "contact", "services"):
            for url, cat in candidates.items():
                if cat == category and url not in chosen:
                    chosen.append(url)
                    break

        for url in chosen[:3]:
            text = self.fetch(url)
            if text:
                self.pages[url] = text

    # ------------------------------------------------------------------
    # Field extraction
    # ------------------------------------------------------------------

    @staticmethod
    def _clean(value: Optional[str]) -> str:
        return " ".join((value or "").split())

    def extract_emails(self, home_html: str, texts: str) -> list[str]:
        found = re.findall(
            r"[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}",
            f"{home_html} {texts}",
        )
        # Drop image filenames / asset lookalikes
        skip = (".png", ".jpg", ".jpeg", ".gif", ".webp", ".svg", ".css", ".js")
        seen: set[str] = set()
        emails: list[str] = []
        for email in found:
            low = email.lower()
            if low.endswith(skip) or low in seen:
                continue
            seen.add(low)
            emails.append(email)
        return emails[:5]

    def extract_phones(self, home_html: str, texts: str) -> list[str]:
        # tel: links are the most reliable signal
        tel_links = re.findall(
            r'href=["\']tel:([^"\']+)["\']', home_html, re.IGNORECASE
        )
        phones: list[str] = []
        for raw in tel_links:
            phone = self._clean(raw)
            if phone and phone not in phones:
                phones.append(phone)
        if not phones:
            # Fall back to visible international-format numbers in text
            pattern = re.compile(r"\+?\d[\d\s\-()]{7,15}\d")
            for match in pattern.finditer(texts):
                phone = self._clean(match.group(0))
                digits = re.sub(r"\D", "", phone)
                if 9 <= len(digits) <= 15 and phone not in phones:
                    phones.append(phone)
        return phones[:5]

    def extract_address(self, texts: str) -> str:
        # Look for lines following address keywords
        pattern = re.compile(
            r"(?:address|located at|location|find us at|our office[s]?|headquarters)[:\s]*([^\n|]{10,160})",
            re.IGNORECASE,
        )
        match = pattern.search(texts)
        if match:
            address = self._clean(match.group(1))
            # Cut footer junk that often follows the address in one text blob
            for marker in (" Email", " Phone", " Copyright", " ©", " All rights"):
                idx = address.find(marker)
                if idx > 0:
                    address = address[:idx]
            return address[:200]
        # Fallback: P.O. Box pattern common in Tanzania
        po_match = re.search(
            r"(?:p\.?\s*o\.?\s*box\s*[\w\s,]{2,60}(?:tanzania|dar es salaam|dodoma|arusha|mwanza)?)",
            texts,
            re.IGNORECASE,
        )
        if po_match:
            return self._clean(po_match.group(0))[:200]
        return ""

    def extract_services(self, texts: str) -> str:
        # 1) Single-line form: "Our Services: web design, hosting, SEO"
        pattern = re.compile(
            r"^[ \t]*(?:our services|services(?: offered| include)?|what we do|our products|solutions we offer)[\s:]+([^\n]{10,400})$",
            re.IGNORECASE | re.MULTILINE,
        )
        for match in pattern.finditer(texts):
            value = self._clean(match.group(1))
            if value:
                return value[:400]

        # 2) List form: a "Services" heading followed by short <li>-style
        # lines. Common on modern sites where each service is its own card.
        lines = [ln.strip() for ln in texts.splitlines() if ln.strip()]
        for idx, line in enumerate(lines):
            if not re.fullmatch(
                r"(?:our )?services(?: & products| and products)?|what we do|our solutions",
                line, re.IGNORECASE,
            ):
                continue
            items: list[str] = []
            for nxt in lines[idx + 1 : idx + 15]:
                # stop when the section clearly ends
                if re.fullmatch(
                    r"(?:our )?(?:about(?: us)?|contact(?: us)?|why choose(?: us)?|get in touch|portfolio|testimonials|faq|blog|home)?",
                    nxt, re.IGNORECASE,
                ) and nxt:
                    break
                if 3 <= len(nxt) <= 80 and not re.search(r"@|\+\d|copyright|©", nxt, re.IGNORECASE):
                    items.append(nxt.rstrip(".,;"))
                if len(items) >= 8:
                    break
            if len(items) >= 2:
                return ", ".join(items)[:400]
        return ""

    def extract_about(self, texts: str) -> str:
        # 1) Single-line form: "About Us: We are a ..."
        pattern = re.compile(
            r"^[ \t]*(?:about(?: us)?|who we are|our story|company profile|overview)[\s:]+([^\n]{20,500})$",
            re.IGNORECASE | re.MULTILINE,
        )
        for match in pattern.finditer(texts):
            value = self._clean(match.group(1))
            if value:
                return value[:500]

        # 2) Heading followed by a paragraph line ("About Us" on its own line,
        # then the description as the next line).
        lines = [ln.strip() for ln in texts.splitlines() if ln.strip()]
        for idx, line in enumerate(lines):
            if re.fullmatch(r"about(?: us)?|who we are|our story", line, re.IGNORECASE):
                for nxt in lines[idx + 1 : idx + 3]:
                    if 40 <= len(nxt) <= 500 and not re.search(r"@|\+\d|copyright|©", nxt, re.IGNORECASE):
                        return nxt[:500]
        return ""

    def extract_business_name(self, home_html: str) -> str:
        # <title> before a separator is the usual business name
        title_match = re.search(r"<title[^>]*>([^<]+)</title>", home_html, re.IGNORECASE)
        if title_match:
            title = self._clean(html_lib.unescape(title_match.group(1)))
            for sep in ("|", "–", "—", "::", " - "):
                if sep in title:
                    title = title.split(sep)[0].strip()
                    break
            if title:
                return title[:120]
        return ""

    # ------------------------------------------------------------------
    # Directory harvesting (auto-discovery of new scrape targets)
    # ------------------------------------------------------------------

    def discover_profile_links(self, home_html: str) -> list[str]:
        """
        Return distinct INTERNAL profile-page URLs (e.g. /company/123/Name)
        when the page contains many of them — the signature of a modern,
        JavaScript-rendered business directory.
        """
        extractor = TextExtractor()
        try:
            extractor.feed(home_html)
        except Exception:
            return []

        self_hosts = {self.host}
        bare = self.host[4:] if self.host.startswith("www.") else self.host
        self_hosts |= {bare, f"www.{bare}"}

        profiles: dict[str, None] = {}  # ordered set
        for href, _anchor in extractor.links:
            if not href or href.startswith(("mailto:", "tel:", "javascript:", "#", "data:")):
                continue
            parsed = urllib.parse.urlparse(urllib.parse.urljoin(self.url, href))
            host = parsed.netloc.lower().split(":")[0]
            if host not in self_hosts or not PROFILE_PATH_PATTERN.match(parsed.path or ""):
                continue
            profiles.setdefault(f"{parsed.scheme}://{parsed.netloc}{parsed.path}", None)

        urls = list(profiles)
        return urls if len(urls) >= PROFILE_MIN_LINKS else []

    def _profile_external_website(self, profile_html: str) -> Optional[str]:
        """
        Pull the company's real external homepage out of one directory
        profile page: the first absolute http(s) link that is not this
        directory, social media, an asset host or a document link.
        """
        extractor = TextExtractor()
        try:
            extractor.feed(profile_html)
        except Exception:
            return None

        self_hosts = {self.host}
        bare = self.host[4:] if self.host.startswith("www.") else self.host
        self_hosts |= {bare, f"www.{bare}"}

        for href, _anchor in extractor.links:
            if not href or href.startswith(("mailto:", "tel:", "javascript:", "#", "data:")):
                continue
            if not href.startswith(("http://", "https://")):
                continue
            parsed = urllib.parse.urlparse(href)
            host = parsed.netloc.lower().split(":")[0]
            if not host or "." not in host or host in self_hosts:
                continue
            if _is_non_business_host(host):
                continue
            if parsed.path.lower().endswith(NON_BUSINESS_EXTENSIONS):
                continue
            return f"{parsed.scheme}://{parsed.netloc}/"

        return None

    def harvest_profile_businesses(self, home_html: str) -> list[str]:
        """
        For profile-based directories: sample profile pages and return the
        external business homepages found on them (deduped by domain).
        """
        profile_urls = self.discover_profile_links(home_html)
        if not profile_urls:
            return []

        found: dict[str, str] = {}  # external domain -> homepage url
        for profile_url in profile_urls[:PROFILE_SAMPLE_LIMIT]:
            # fetch() populates raw_pages on success; robots/HTTP blocks just skip
            self.fetch(profile_url)
            raw = self.raw_pages.get(profile_url)
            if not raw:
                continue
            external = self._profile_external_website(raw)
            if not external:
                continue
            host = urllib.parse.urlparse(external).netloc.lower()
            found.setdefault(host, external)

        return list(found.values())

    def harvest_directory_links(self, home_html: str) -> list[str]:
        """
        When the scraped page looks like a business directory, return
        outbound business homepage URLs.

        Two directory shapes are supported:
          - classic listing pages: many links to DISTINCT external domains
          - profile-based directories (modern / SPA): many internal profile
            pages, each linking to the business's real external website

        Returns [] for normal business sites so they seed nothing.
        """
        extractor = TextExtractor()
        try:
            extractor.feed(home_html)
        except Exception:
            return []

        # Treat www/non-www variants of our own host as "self"
        bare = self.host[4:] if self.host.startswith("www.") else self.host
        self_hosts = {self.host, bare, f"www.{bare}"}

        by_domain: dict[str, str] = {}
        total_external = 0

        for href, _anchor in extractor.links:
            if not href or href.startswith(("mailto:", "tel:", "javascript:", "#", "data:")):
                continue
            absolute = urllib.parse.urljoin(self.url, href)
            if not absolute.startswith(("http://", "https://")):
                continue
            parsed = urllib.parse.urlparse(absolute)
            host = parsed.netloc.lower().split(":")[0]
            if not host or "." not in host or not re.match(r"^[a-z0-9.-]+\.[a-z]{2,}$", host):
                continue
            if host in self_hosts:
                continue
            if _is_non_business_host(host):
                continue
            if parsed.path.lower().endswith(NON_BUSINESS_EXTENSIONS):
                continue

            total_external += 1
            # One candidate per domain, normalized to the site root so
            # deep links to the same business don't duplicate targets.
            by_domain.setdefault(host, f"{parsed.scheme}://{parsed.netloc}/")

        if (
            len(by_domain) >= DIRECTORY_MIN_DISTINCT_DOMAINS
            and total_external >= DIRECTORY_MIN_EXTERNAL_LINKS
        ):
            return sorted(by_domain.values())[:MAX_DISCOVERED_LINKS]

        # Fallback: profile-based directory (modern / SPA listing pages).
        if len(by_domain) < DIRECTORY_MIN_DISTINCT_DOMAINS:
            return self.harvest_profile_businesses(home_html)[:MAX_DISCOVERED_LINKS]

        return []

    # ------------------------------------------------------------------
    # Main entry
    # ------------------------------------------------------------------

    def scrape(self) -> dict[str, Any]:
        if not self.robots_allows(self.url):
            return {
                "success": False,
                "message": "Website disallows crawling via robots.txt",
            }

        _rate_limit(self.host)
        try:
            resp = self.session.get(self.url, timeout=TIMEOUT, allow_redirects=True)
        except Exception as exc:
            return {"success": False, "message": f"Could not reach website: {exc}"}

        if resp.status_code != 200:
            return {
                "success": False,
                "message": f"Website returned HTTP {resp.status_code}",
            }
        if "text/html" not in resp.headers.get("content-type", "html").lower():
            return {"success": False, "message": "Website did not return an HTML page"}

        home_html = resp.text

        # JavaScript-shell detection → re-fetch through headless Chromium so
        # SPA content (services, descriptions, contacts) becomes extractable.
        if _looks_like_js_shell(home_html):
            rendered = _renderer.render(self.url)
            if rendered and len(rendered) > len(home_html) * 1.2:
                home_html = rendered
                self.render_mode = "headless-browser"
                self._js_rendered = True

        self.pages[self.url] = _strip_tags_lines(home_html)
        self.raw_pages[self.url] = home_html

        # Pull internal about/contact/services pages (rate-limited)
        self.discover_pages(home_html)

        # Extraction pass over everything fetched so far. Defined as a local
        # function so it can be RE-RUN after a late headless render (some SPA
        # shells carry enough nav text to skip the first render check, yet
        # keep their real content JavaScript-only).
        def run_extraction() -> dict[str, Any]:
            # Combined RAW html of every fetched page (home + about/contact/...):
            # contact details live in any page's markup (tel:/mailto: links), not
            # just the home page. `texts` is line-preserving so the line-anchored
            # services/about/address regexes can match.
            all_raw = "\n".join(self.raw_pages.values())
            texts = "\n".join(self.pages.values())
            flat_texts = " ".join(" ".join(self.pages.values()).split())

            # Reviews/testimonials/ratings from the target's own public pages
            html_pages = list(self.raw_pages.values())
            review_data = ReviewExtractor().extract(html_pages, texts)

            # Structured schema.org data first (most reliable), regex fallbacks second
            structured = ReviewExtractor().extract_business_jsonld(html_pages)

            emails = self.extract_emails(all_raw, texts)
            if structured["email"] and structured["email"].lower() not in {e.lower() for e in emails}:
                emails = [structured["email"]] + emails

            phones = self.extract_phones(all_raw, texts)
            if structured["phone"] and structured["phone"] not in phones:
                phones = [structured["phone"]] + phones

            business_name = self.extract_business_name(self.raw_pages.get(self.url, "")) or structured["business_name"]
            address = structured["address"] or (self.extract_address(texts) or self.extract_address(flat_texts))
            services = structured["services"]
            if not services and structured["service_nodes"]:
                services = ", ".join(structured["service_nodes"])
            if not services:
                services = self.extract_services(texts)
            about = structured["about"] or self.extract_about(texts)

            # httpObfuscation guard: emails written as "name (at) domain"
            if not emails:
                obfuscated = re.search(
                    r"([\w.+-]+)\s*[\(\[]at[\)\]]\s*([\w.-]+)\s*[\(\[]dot[\)\]]\s*(\w+)",
                    texts, re.IGNORECASE,
                )
                if obfuscated:
                    emails.append(f"{obfuscated.group(1)}@{obfuscated.group(2)}.{obfuscated.group(3)}")

            return {
                "all_raw": all_raw,
                "texts": texts,
                "review_data": review_data,
                "emails": emails,
                "phones": phones,
                "business_name": business_name,
                "address": address,
                "services": services,
                "about": about,
            }

        extracted = run_extraction()

        # Second chance for content-JS-only sites: the home page was never
        # rendered, both description fields came back empty, and the page
        # carries SPA markers. One Chromium render, then re-extract.
        if (
            self.render_mode == "http"
            and not extracted["services"]
            and not extracted["about"]
            and _has_spa_markers(home_html)
        ):
            rendered = _renderer.render(self.url)
            if rendered and len(rendered) > len(home_html):
                home_html = rendered
                self.raw_pages[self.url] = home_html
                self.pages[self.url] = _strip_tags_lines(home_html)
                self.render_mode = "headless-browser"
                self._js_rendered = True
                extracted = run_extraction()

        all_raw = extracted["all_raw"]
        texts = extracted["texts"]
        emails = extracted["emails"]
        phones = extracted["phones"]
        business_name = extracted["business_name"]
        address = extracted["address"]
        services = extracted["services"]
        about = extracted["about"]

        # Directory harvesting: if this page lists many external business
        # sites (a directory), report them so Laravel can queue new targets.
        discovered_links = self.harvest_directory_links(home_html)

        # Preliminary gaps/opportunities from the home page we already have —
        # instant triage for staff BEFORE running a full health scan.
        https_ok = self.url.startswith("https://")
        preliminary_gaps = GapDetector.detect(
            home_html=home_html,
            texts=texts,
            https_ok=https_ok,
            emails=emails,
            phones=phones,
            all_html=all_raw,
        )

        return {
            "success": True,
            "url": self.url,
            "business_name": business_name,
            "email": emails[0] if emails else "",
            "extra_emails": emails[1:3],
            "phone": phones[0] if phones else "",
            "extra_phones": phones[1:3],
            "address": address,
            "services": services,
            "about": about,
            "reviews": extracted["review_data"]["reviews"],
            "rating_avg": extracted["review_data"]["rating_avg"],
            "rating_count": extracted["review_data"]["rating_count"],
            "rating_source": extracted["review_data"]["rating_source"],
            "pages_scraped": sorted(self.pages.keys()),
            "render_mode": self.render_mode,
            "discovered_links": discovered_links,
            "preliminary_gaps": preliminary_gaps,
            "scraped_at": time.strftime("%Y-%m-%dT%H:%M:%SZ", time.gmtime()),
        }


def scrape_url(url: str) -> dict[str, Any]:
    """Validate and scrape one public website. Public entry point."""
    normalized = _normalize_url(url)
    if not normalized:
        return {"success": False, "message": "Invalid or non-public URL"}
    return BusinessScraper(normalized).scrape()


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    import json
    import sys

    if len(sys.argv) != 2:
        print("Usage: python business_scraper.py <url>")
        sys.exit(1)

    result = scrape_url(sys.argv[1])
    print(json.dumps(result, indent=2))
    sys.exit(0 if result.get("success") else 1)
