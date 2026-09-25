#!/usr/bin/env python3
"""
Oweru Discovery - Step 1/2 probe: OpenStreetMap (Overpass API) -> website URLs.

Deliberately STANDALONE (stdlib only, no Flask, no database) so the discovery
source can be proven from the command line before anything is wired into
Laravel (Step 3) or the scraper (Step 4).

What it does:
  1. Geocode a city name to a bounding box (Nominatim), or take --bbox.
  2. Ask Overpass for businesses of a category inside that box.
  3. Extract the official website URL (website / contact:website / url /
     contact:url tags), normalize it, skip social/directory links.
  4. Dedupe by host (one site = one record, mirrors the project's hostKey rule).
  5. Print the URLs - and, on purpose, ALSO count the businesses that have NO
     website at all: for Oweru those are the "we build you a website" leads.

Usage:
    C:/python312/python.exe scanner/osm_discovery.py --city "Dar es Salaam" --category hotel
    C:/python312/python.exe scanner/osm_discovery.py --city "Dar es Salaam" --category all --limit 400
    C:/python312/python.exe scanner/osm_discovery.py --bbox -7.0,39.0,-6.6,39.4 --category restaurant
    C:/python312/python.exe scanner/osm_discovery.py --list-categories

Machine-readable output (this is the future Step-3 contract with Laravel):
    ... --json

Responsible use: one Overpass query per run, 90s server-side timeout, and a
fallback mirror. Public Overpass instances are shared infrastructure - do not
loop this script.
"""

from __future__ import annotations

import argparse
import json
import re
import sys
import time
import urllib.error
import urllib.parse
import urllib.request

# ---------------------------------------------------------------------------
# Configuration
# ---------------------------------------------------------------------------

USER_AGENT = "OweruDiscovery/0.1 (+https://oweru.co.tz; discovery@oweru.co.tz)"
NOMINATIM_URL = "https://nominatim.openstreetmap.org/search"
OVERPASS_ENDPOINTS = [
    "https://overpass-api.de/api/interpreter",
    "https://overpass.kumi.systems/api/interpreter",
]
GEOCODE_TIMEOUT = 30  # seconds
OVERPASS_TIMEOUT = 120  # seconds

# OSM tags that can carry a business's official website.
WEBSITE_TAGS = ("website", "contact:website", "url", "contact:url")

# Hosts that are NOT an official business website (mappers often put a social
# or booking-platform link in website=*). Skipped, but counted in the summary.
NON_BUSINESS_HOSTS = (
    "facebook.com", "instagram.com", "twitter.com", "x.com", "threads.net",
    "whatsapp.com", "wa.me", "youtube.com", "youtu.be", "linkedin.com",
    "tiktok.com", "t.me", "telegram.me", "snapchat.com", "pinterest.com",
    "google.com", "goo.gl", "maps.app.goo.gl", "waze.com",
    "tripadvisor.com", "booking.com", "airbnb.com", "expedia.com",
    "jiji.co.tz", "yellowpages.co.tz", "zoom.tanzania", "zoomtanzania.com",
)

# Category -> Overpass tag filters (OR-ed together). Extend freely; the "all"
# category is built at runtime as "any named record carrying a website tag".
CATEGORIES: dict[str, list[str]] = {
    "hotel": ['["tourism"="hotel"]'],
    "guesthouse": ['["tourism"="guest_house"]'],
    "hostel": ['["tourism"="hostel"]'],
    "lodge": ['["tourism"="lodge"]'],
    "restaurant": ['["amenity"="restaurant"]'],
    "cafe": ['["amenity"="cafe"]'],
    "bar": ['["amenity"="bar"]', '["amenity"="pub"]'],
    "supermarket": ['["shop"="supermarket"]'],
    "shop": ['["shop"]'],
    "school": ['["amenity"="school"]'],
    "hospital": ['["amenity"="hospital"]', '["amenity"="clinic"]'],
    "pharmacy": ['["amenity"="pharmacy"]'],
    "bank": ['["amenity"="bank"]'],
    "travel": ['["shop"="travel_agency"]', '["office"="travel_agent"]'],
    "office": ['["office"]'],
    "garage": ['["shop"="car_repair"]'],
    "salon": ['["shop"="hairdresser"]', '["shop"="beauty"]'],
    "all": [],
}

# ---------------------------------------------------------------------------
# HTTP helpers (stdlib only)
# ---------------------------------------------------------------------------


def _http_json(url: str, timeout: int, data: bytes | None = None) -> dict:
    req = urllib.request.Request(
        url,
        data=data,
        headers={"User-Agent": USER_AGENT, "Accept": "application/json"},
    )
    with urllib.request.urlopen(req, timeout=timeout) as resp:
        return json.loads(resp.read().decode("utf-8", "replace"))


def geocode(city: str) -> tuple[float, float, float, float]:
    """City name -> (south, west, north, east) bounding box via Nominatim."""
    qs = urllib.parse.urlencode({"q": city, "format": "json", "limit": "1"})
    data = _http_json(f"{NOMINATIM_URL}?{qs}", GEOCODE_TIMEOUT)
    if not data:
        raise SystemExit(f"Could not geocode '{city}' (Nominatim returned nothing).")
    bb = data[0].get("boundingbox")
    if not bb or len(bb) != 4:
        raise SystemExit(f"Geocoded '{city}' but no bounding box came back.")
    south, north, west, east = (float(x) for x in bb)  # Nominatim order: S,N,W,E
    return south, west, north, east


def build_query(filters: list[str], bbox: tuple[float, float, float, float], limit: int) -> str:
    """Overpass QL: every filter OR-ed together inside the bbox."""
    south, west, north, east = bbox
    box = f"{south},{west},{north},{east}"
    body = "\n".join(f"  nwr{f}({box});" for f in filters)
    return f"[out:json][timeout:90];\n(\n{body}\n);\nout tags center {limit};"


def run_overpass(query: str) -> dict:
    """POST the query, trying mirrors in order (one polite retry each)."""
    payload = urllib.parse.urlencode({"data": query}).encode("utf-8")
    last_error: Exception | None = None
    for endpoint in OVERPASS_ENDPOINTS:
        for attempt in (1, 2):
            try:
                req = urllib.request.Request(
                    endpoint,
                    data=payload,
                    headers={
                        "User-Agent": USER_AGENT,
                        "Content-Type": "application/x-www-form-urlencoded",
                    },
                )
                with urllib.request.urlopen(req, timeout=OVERPASS_TIMEOUT) as resp:
                    return json.loads(resp.read().decode("utf-8", "replace"))
            except Exception as exc:  # noqa: BLE001 - retry once, then next mirror
                last_error = exc
                print(f"  endpoint failed ({endpoint}, attempt {attempt}): {exc}", file=sys.stderr)
                if attempt == 1:
                    time.sleep(5)  # shared instances: back off briefly, don't hammer
    raise SystemExit(f"All Overpass endpoints failed. Last error: {last_error}")


# ---------------------------------------------------------------------------
# Extraction
# ---------------------------------------------------------------------------


def _is_non_business(host: str) -> bool:
    return any(host == h or host.endswith("." + h) for h in NON_BUSINESS_HOSTS)


def normalize_url(raw: str) -> str | None:
    """Best-effort cleanup of an OSM website tag value. None = unusable."""
    raw = (raw or "").strip().strip('"').strip("'").strip()
    if not raw or "@" in raw or " " in raw:
        return None
    if raw.startswith("//"):
        raw = "https:" + raw
    if not re.match(r"^https?://", raw, re.IGNORECASE):
        raw = "https://" + raw
    try:
        parsed = urllib.parse.urlparse(raw)
    except ValueError:
        return None
    host = (parsed.hostname or "").lower().rstrip(".")
    if not host or "." not in host or len(host) < 4:
        return None
    if host.startswith("www."):
        host = host[4:]  # project URL-identity rule: no www prefix
    path = parsed.path if parsed.path not in ("", "/") else ""
    rebuilt = f"https://{host}{path}"
    if parsed.query:
        rebuilt += "?" + parsed.query
    return rebuilt


def extract(elements: list[dict]) -> dict:
    """Split Overpass elements into unique website URLs + no-website leads."""
    websites: list[dict] = []
    no_website: list[dict] = []
    seen_hosts: set[str] = set()
    stats = {
        "osm_records": 0,
        "with_website_tag": 0,
        "without_website": 0,
        "invalid_website_urls": 0,
        "skipped_non_business_links": 0,
        "duplicate_hosts_merged": 0,
        "unique_websites": 0,
    }

    for el in elements:
        tags = el.get("tags") or {}
        name = (tags.get("name") or "").strip()
        stats["osm_records"] += 1

        record: dict = {"name": name or None, "osm_type": el.get("type", "?")}
        center = el.get("center") or (
            {"lat": el["lat"], "lon": el["lon"]} if "lat" in el and "lon" in el else None
        )
        if center and center.get("lat") is not None:
            record["lat"] = round(center["lat"], 6)
            record["lon"] = round(center["lon"], 6)

        raw_site = next((tags[t] for t in WEBSITE_TAGS if tags.get(t)), None)
        if not raw_site:
            if name:
                stats["without_website"] += 1
                if len(no_website) < 100:  # sample only, keeps output small
                    no_website.append({"name": name, "osm_type": record["osm_type"]})
            continue

        stats["with_website_tag"] += 1
        url = normalize_url(raw_site)
        if not url:
            stats["invalid_website_urls"] += 1
            continue
        host = urllib.parse.urlparse(url).hostname or ""
        if _is_non_business(host):
            stats["skipped_non_business_links"] += 1
            continue
        if host in seen_hosts:
            stats["duplicate_hosts_merged"] += 1
            continue
        seen_hosts.add(host)

        record.update({"url": url, "host": host})
        websites.append(record)

    stats["unique_websites"] = len(websites)
    return {"stats": stats, "websites": websites, "no_website_sample": no_website}


# ---------------------------------------------------------------------------
# CLI
# ---------------------------------------------------------------------------


def main() -> None:
    try:
        sys.stdout.reconfigure(errors="replace")  # Windows console safety
    except Exception:
        pass

    parser = argparse.ArgumentParser(description="Oweru discovery probe (OSM/Overpass)")
    parser.add_argument("--city", help='e.g. "Dar es Salaam"')
    parser.add_argument("--bbox", help="south,west,north,east (skips geocoding)")
    parser.add_argument("--category", default="hotel", help="see --list-categories")
    parser.add_argument("--limit", type=int, default=300, help="max OSM records to fetch")
    parser.add_argument("--json", action="store_true", help="machine-readable output")
    parser.add_argument("--list-categories", action="store_true")
    args = parser.parse_args()

    if args.list_categories:
        print("Available categories:")
        for name in CATEGORIES:
            print(f"  {name}")
        return

    if bool(args.city) == bool(args.bbox):
        raise SystemExit("Provide exactly one of --city or --bbox.")

    filters = CATEGORIES.get(args.category)
    if filters is None:
        raise SystemExit(f"Unknown category '{args.category}'. Try --list-categories.")
    if args.category == "all":
        # ONE pass over the bbox instead of one scan per website tag - four
        # full-bbox scans 504 on the shared public Overpass instances.
        tag_regex = "|".join(re.escape(t) for t in WEBSITE_TAGS)
        filters = [f'[name][~"^{tag_regex}$"~"."]']

    if args.bbox:
        try:
            south, west, north, east = (float(x) for x in args.bbox.split(","))
        except ValueError:
            raise SystemExit("--bbox must be south,west,north,east (decimal degrees)")
        bbox = (south, west, north, east)
    else:
        print(f"Geocoding '{args.city}' ...")
        bbox = geocode(args.city)

    label = args.city or f"bbox {args.bbox}"
    print(f"Searching {label} for: {args.category} ...")
    query = build_query(filters, bbox, args.limit)
    data = run_overpass(query)
    result = extract(data.get("elements", []))
    result["query"] = {
        "city": args.city,
        "bbox": list(bbox),
        "category": args.category,
        "limit": args.limit,
    }

    if args.json:
        print(json.dumps(result, indent=2, ensure_ascii=True))
        return

    stats = result["stats"]
    print(
        f"\nFound {stats['osm_records']} OSM records "
        f"({stats['with_website_tag']} carry a website tag)\n"
    )

    websites = result["websites"]
    if websites:
        print(f"Website URLs ({stats['unique_websites']} unique):")
        for w in websites:
            name = f"  - {w['name']}" if w.get("name") else ""
            print(f"  {w['url']}{name}")
    else:
        print("Website URLs: none found.")

    print("\nSummary:")
    print(f"  OSM records:             {stats['osm_records']}")
    print(f"  With website tag:        {stats['with_website_tag']}")
    print(f"  Unique websites:         {stats['unique_websites']}  <- discovery output")
    print(f"  No website listed:       {stats['without_website']}  <- 'we build you a site' leads")
    print(f"  Invalid website tags:    {stats['invalid_website_urls']}")
    print(f"  Social/directory links:  {stats['skipped_non_business_links']} (skipped)")
    print(f"  Duplicate hosts merged:  {stats['duplicate_hosts_merged']}")

    sample = result["no_website_sample"]
    if sample:
        names = ", ".join(w["name"] for w in sample[:10])
        more = f" ... (+{stats['without_website'] - 10} more)" if stats["without_website"] > 10 else ""
        print(f"\nNo-website sample: {names}{more}")


if __name__ == "__main__":
    main()
