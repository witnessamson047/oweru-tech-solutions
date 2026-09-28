# 📋 PROJECT STATUS — read me first (for Buffy / any developer)

**Project:** Oweru Tech Solutions — website health scanner + lead pipeline
**Owner:** Witness (GitHub: witnessamson047)
**Last updated:** 2026-09-25 (Steps 3+6 DONE: Website Discovery fully wired — 96 Dar websites queued + 91 no-website outreach leads captured live through the admin form; see top section)

## Website discovery via OpenStreetMap (NEW 2026-09-25) — Steps 1-3 DONE, live end-to-end
Direction agreed: the client has zero technical exposure, so nobody should ever type a URL — staff
pick a city + category, and discovered business websites feed the EXISTING scrape → auto-scan
pipeline. CSV export and payments are explicitly OUT of scope for this module.

**Steps 1+2 DONE — `scanner/osm_discovery.py` (standalone probe; stdlib only, no Flask, no DB):**
Nominatim geocodes the city to a bbox → ONE Overpass query (90s timeout, --limit cap) pulls named
businesses carrying a website tag (website / contact:website / url / contact:url) → URLs are
https-canonicalized, www-stripped, deduped by host (same hostKey rule as ScrapeTarget), and
social/booking links (facebook, tripadvisor, booking.com, goo.gl, …) are skipped and counted.
Run it: `C:\python312\python.exe scanner\osm_discovery.py --city "Dar es Salaam" --category all`
(--list-categories for the set: hotel, guesthouse, restaurant, cafe, school, shop, office, all, …;
--bbox skips geocoding; --json is the future Laravel contract).

**Live results (Dar es Salaam, verified 2026-09-25):**
- hotels:      300 OSM records →   7 unique website URLs (249 businesses have NO website)
- restaurants: 300 OSM records →   4 unique website URLs (124 have NO website)
- all:         332 OSM records → 262 unique website URLs (62 dup hosts merged, 5 social links skipped)
URLs are real, scrapeable businesses: crdbbank.co.tz, kfc.co.tz, blackwoodaparthotel.co.tz,
regencymedicalcentre.com, epidor.co.tz …

- KEY INSIGHT: per-category fill is THIN (~3% of OSM hotels/restaurants carry a website tag). The
  productive discovery unit is a CITY-WIDE harvest (262 sites in one query); categories are a bonus
  filter, not the main event.
- The no-website counter is a sales goldmine: 249 Dar hotels + 124 Dar restaurants with NO website
  at all = ready-made "we build you one" outreach lists (the probe reports them on purpose).
- Reliability: the first broad query (4 bbox scans OR-ed) 504'd on BOTH public Overpass endpoints;
  fixed by making "all" ONE pass (regex key filter) + one polite retry per endpoint (5s backoff).
  The script then self-recovered from live 504s twice. Public Overpass is shared infrastructure:
  one query per run, never loop it (etiquette mirrors the scraper's 2s/host rate limit).

**Step 3 DONE (2026-09-25) — wired into Laravel (decision: SHELL-OUT, not Flask):** DiscoveryService
runs the probe via Symfony Process with the python path in config — discovery is an occasional
admin-triggered public-Overpass query, not part of the 24/7 engine loop, so it must not depend on
Flask being up. Pieces:
- `discovery_runs` table + `scrape_targets.discovery_run_id` provenance column (migration
  2026_09_25_000001); DiscoveryRun model (status running/completed/failed, stats+result JSON
  snapshots, error text, queued/skipped counters).
- `DiscoveryService::make()` (ScraperClient-style scalar-params trap honored: command + controller
  call make(), NOT method injection; make() honors swapped instances so tests stay network-free).
  run(): creates the run row → executes the probe → queues every URL through
  ScrapeTarget::normalizeUrl + hostKey dedupe (host keys loaded ONCE per run) with
  discovery_run_id attached. Failures never throw: they land on run.status=failed + run.error.
- `php artisan discovery:run --city="Dar es Salaam" [--category=hotel] [--limit=200] [--stats]`.
- Admin UI: /admin/discovery — the client wireframe (Location with TZ-city suggestions /
  Category dropdown / Max records → 🔍 DISCOVER, button disables itself while running) + stats
  cards + full run history. Sidebar: "Website Discovery" above Scraper. POST runs synchronously
  (probe is 30-120s) like the scan actions do.
- `DiscoveryJob` (queued variant for future scheduler/batch use; clamps config timeout ≤270s
  inside its own 280s queue timeout so a killed job cannot strand a run row).
- Config `owers.discovery.*` (python_bin C:\python312\python.exe, script path, timeout 480s =
  worst-case geocode + 2 endpoints × 2 attempts × 100s, default/max limit, category + city
  suggestion lists) + OWERU_DISCOVERY_* in .env.example.
- **Contract fix in the probe:** --json mode now emits progress to STDERR and PURE JSON on
  stdout (Laravel parses stdout; the "Geocoding…" lines were corrupting the payload — found by
  the live shell-out test).
- Tests: tests/Feature/DiscoveryTest.php (8 — queue+provenance, dedupe skip, existing-target
  skip, failure recording, artisan command, admin form render+store+validation, queueable job).
  Suite now at **107 passed (426 assertions)**.
- VERIFIED LIVE end-to-end: `discovery:run --city="Dar es Salaam" --limit=100` → probe ran →
  run #3 completed → 96 unique websites → 96 scrape_targets rows created with
  discovery_run_id=3, status=active (0 skipped). scrape:run's normal passes now scrape them
  (batch 10/pass) and AutoScanJob health-scans the new businesses (cap 5/pass) — with the
  engine + scheduler running via start.bat.

**Step 4 (scraper) + Step 5 (scanner): FREE as planned — discovered targets are ordinary
scrape_targets rows; the existing 24/7 queue + directory harvesting + auto-scan absorb them
unchanged. (The 96 run-#3 targets process at the normal cadence: scrape:run batch 10/pass or
the Check All Now button.)**

**Step 6 DONE (2026-09-25) — dashboard surface + the no-website outreach list:**
- **`discovery_leads` table** (migration 2026_09_25_000002): businesses found by discovery with
  NO website at all — the "we'll build you one" outreach list. Deduped by (business_name, city)
  so repeated runs extend/refresh the list, never duplicate it; attribution follows the freshest
  run; status new/contacted + contacted_at + notes; lat/lon kept for a 📍 Google Maps link.
  `discovery_runs.leads_count` records each run's contribution.
- **Probe upgrade:** `no_website` now collects up to 500 records WITH coordinates (was a 100-row
  name-only sample; category="all" runs only ever return website-carrying records, so no-website
  leads come from category runs like restaurants: 249 hotels / 124 restaurants in Dar alone).
- **/admin/discovery/leads** (sidebar "No-Website Leads"): outreach list with search/status
  filter, 📍 map links, "✓ Mark contacted" per row, stats cards. Runs page shows lead counts.
- **Dashboard:** "Website Discovery" panel — websites discovered this week, businesses with NO
  website found, leads contacted, last 5 runs (city/category/queued/leads or failure reason) —
  plus a navy "No-Website Outreach" card with the call-to-action count. Sidebar + dashboard
  quick-links include Discovery.
- **WINDOWS DNS QUIRK (important operational finding):** python spawned from the long-running
  `php artisan serve` process cannot resolve DNS — "[Errno 11003] getaddrinfo failed" for every
  host (Nominatim AND Overpass), while the same probe from a fresh terminal/queue-worker lineage
  works fine. WinSock per-process quirk; NOT proxy env (none set). Fixes layered in:
  (1) built-in bbox table for all 12 suggested TZ cities (no geocoding call at all);
  (2) probe geocode retries once after 5s; (3) probe ignores inherited proxy vars (ProxyHandler({}));
  (4) probe accepts OWERU_DNS_OVERRIDES host=ip pairs and patches socket.getaddrinfo
  (TLS/SNI unaffected); (5) **the real fix: the admin form now QUEUES the run** — run row created
  instantly (0.5s form response, status=running visible), DiscoveryJob executes on the queue
  worker whose lineage resolves DNS fine (same worker as every scrape/scan; failed() hook marks
  the run failed if the job dies so nothing sticks on 'running'). Artisan discovery:run stays
  synchronous (works fine from terminals/scheduler).
- **Error reporting hardened:** failed runs keep stderr head AND the final traceback line (the
  diagnosis used to be truncated away); flash shows first line, full text on the run row.
- Tests: +3 (leads persistence/dedupe/attribution, outreach page render + mark-contacted,
  dashboard stats/runs render; form now asserts DiscoveryJob dispatch). Suite at **110 passed
  (453 assertions)**.
- **VERIFIED LIVE through the real UI (curl session, admin login → discovery form):**
  Dar es Salaam + Restaurants, limit 200 → form 302 in 0.5s → run #9 'running' → queue worker
  drained backlog + ran DiscoveryJob (7s) → completed: 200 OSM restaurants, 4 websites (all
  correctly skipped as already-queued from run #3), **91 no-website leads created** (Lumumba
  Garden Restourant, The Cholla Authetic Indian, Ladha Halisi, …) → dashboard + outreach list
  populated. The discovery → outreach loop is fully closed for the non-technical client.

## URL identity, watchdog button, headless rendering, full mapping (2026-09-24)
**URL-identity rules — one site = one record.** ScrapeTarget::canonicalizeUrl() stores every URL as
https, lowercase host, no "www." prefix, trailing slash on bare roots. ScrapeTarget::hostKey()
(lowered host minus www) is the identity used for dedupe at ALL write paths:
- `www.abc.co.tz` and `abc.co.tz` → SAME site, queued/scraped once
- `abc.co.tz` and `abc.com` → DIFFERENT businesses (the TLD ending is part of the identity — both kept)
- `http://abc.co.tz` → stored as https (the engine follows redirects)
- Bulk-add success message now NAMES the URLs queued and lists skipped duplicates; the queue box
  states the rule in plain language. Dev-DB records were canonicalized in place (7 targets + 6 businesses
  lost their www. prefixes); ScraperClient upsert is slash/www-flexible so old rows update, never duplicate.

**🐕 Check All Now (watchdog button):** POST admin/scrape-targets/check-all dispatches one queued
ScrapeTargetJob per ACTIVE target (tries=1, timeout=280s; handle() builds ScraperClient via make() —
container injection fatals on its scalar constructor params). Results + watchdog events appear as the
auto-restarting worker processes them; button lives on the Auto-Scraper Queue page. First real events
already fired (3 contact_changed — old junk addresses replaced by clean ones after the extraction fix).

**Headless-browser rendering (Playwright, the Build Brief's optional browser tool):**
`HeadlessRenderer` in business_scraper.py lazily launches bundled Chromium (installed:
`C:\python312\python.exe -m pip install playwright && C:\python312\python.exe -m playwright install chromium`).
Triggers: (1) page looks like an SPA shell (little text + SPA markers / #root div), or (2) second-chance
render when services+about both came back empty on a page carrying SPA markers. Thread-safe (sync API
serialized by a lock — Flask runs threaded). Graceful no-op if Playwright/browser missing. ScraperClient
timeout 180→300s to fit worst-case (4 pages × ~30s render). Response exposes render_mode
(http | headless-browser). Subpages render too when the home page needed it.

**Businesses list now shows 'Scraped From':** the exact source URL per record (linked), so staff always
see what the scraper actually fetched. The Prelim. Gaps column also shows the mapped Oweru services
as chips under the gap badge (top 2 + "+N"; hover title lists the gaps) — ScrapedBusiness
::mappedServices() with recommendations preloaded ONCE per index page (no per-row queries).
**⬇ Export CSV** button on the businesses index (ScrapedBusinessController::export,
route admin.scraped-businesses.export): streamed download respecting the current search/status
filters; columns include contact data, source URL, gaps, priority, mapped services (one
recommendations load for the whole export), latest scan score/band, enquiry #. UTF-8 BOM for Excel,
formula-injection guard (=/+/-/@ prefixes get a ' prefix), chunked (200 rows) for large lists.
Suite: 99 passed (395 assertions) — new test: export downloads CSV with mapped services.

**Recommendation mapping completed: 23/23 checks → service to sell** (was 8/23 — the root cause of
"recommendations not found" on scan pages). RecommendationSeeder extended (Security expiry, mixed
content, mobile typography/tap targets, image sizing, tappable contacts, SEO foundations ×3,
GBP, trust pack ×2, ToS, freshness ×2); re-seeded; scan 245 now maps 9/9 failed checks.

**CRITICAL FIX — scan detail page was 500ing for everyone** (the real "error in health scan" report):
admin/scans/show.blade.php had an unbalanced @if around the pitch-email block (ParseError at :285).
Slipped through because view:cache doesn't execute compiled code and no test renders this page with a
completed scan. Now HTTP 200 with all panels: score+band, score-change vs previous scan, PSI card,
AI-insight card (both appear after PostScanJob runs), check results grouped by area with mapped
"Fix:" service per failed check, top-5 findings, band legend. Walkthrough verified end-to-end:
login → scrape (bare domain) → gaps panel → health scan → scan detail → add-to-leads → enquiry #19.
Suite: 97 passed.

## Doc-conformance pass + human-friendly URL entry (2026-09-24)
Audited against the original Build Brief and closed the remaining gaps:
- **Forgiving URL input everywhere (the "non-technical user" fix):** new App\Support\UrlInput::normalize()
  accepts "modewjifoundation.org", "www.abc.co.tz", quoted/pasted "\"abc.co.tz\"", extra spaces or missing
  https:// — every entry point now works the same way:
  - public scanner API (/api/scanner/scan) + its JS (adds https:// before posting)
  - admin scraper form (POST admin/scraped-businesses)
  - admin website create/edit forms
  - bulk auto-scraper queue already normalized; textarea placeholder now says https:// is optional
  Public scanner + admin forms use type="text" inputmode="url" instead of type="url" so the BROWSER no
  longer blocks human-style input. Invalid input gets one plain-language error: "That does not look like
  a website address. Try something like abc.co.tz — you do not need to type https://" (flashed as both
  the error banner AND a validation error so @error blocks and assertSessionHasErrors keep working).
- **Public free scan now shows the 3 worst findings** (doc §11 said 3; API returned 5) — both scan and
  getStatus endpoints trimmed to 3. The 5-finding version remains the staff/PDF report.
- **Doc-gap item #2 is CLOSED:** scanner weights were already admin-configurable
  (/admin/scanner-checks → ScannerClient::checksPayload, covered by ScannerWeightsTest) and re-scan
  comparison already existed (Scan::scoreChange + improved/declined arrows on the scan page).
- Verified live: POST /api/scanner/scan with bare "example.com" scans fine (score 70, 3 findings);
  UrlInput matrix (domains, quotes, uppercase, spaces, facebook.com → rejected, "abc" → friendly error).
  npm run build clean (new app-CbhB6T2t.js). Suite: 97 passed (385 assertions).

## Scraper extraction overhaul (2026-09-24) — the fixes behind "no emails/services/gaps/watchdog"
User-reported: no emails/phones on scraped sites, watchdog silent, gaps/recommendations/reviews
invisible pre-scan, services never extracted, "where is the scraper?", health-scan error.
Root causes and fixes:
- **Killer bug — line structure was destroyed:** `_strip_tags` collapsed the whole page to ONE line,
  so the line-anchored services/about/address regexes could NEVER match. TextExtractor now tracks
  block elements and `_strip_tags_lines()` returns line-preserving text. Address extraction regressed
  for single-line P.O. Box footers, so it now falls back to flat text too.
- **Contact details now searched across ALL fetched pages:** raw HTML of home + about/contact/services
  pages is combined (tel:/mailto: live on any page, not just home). Ariya Finergy now yields 3 phones.
- **GapDetector hardened:** meta-description check parses attribute-order-independent (<meta name... content...>
  in either order); form/tel/mailto/privacy/payment signals checked against the COMBINED raw HTML of
  all fetched pages, not just home.
- **Gap->Oweru-service mapping in the UI:** ScrapedBusiness::gapsWithRecommendations() pairs each
  preliminary gap with the recommendation row (exact check_name match, then area, then word-overlap),
  so the show page displays "→ Sell: <service> — <solution>" per gap BEFORE any health scan. Gaps panel
  now always renders (explains itself when empty).
- **Re-scrape button:** POST admin/scraped-businesses/{id}/rescrape (ScrapedBusinessController::rescrape)
  refreshes contacts/services/reviews/gaps on demand AND feeds the watchdog — watchdog events need a
  second scrape to diff against the first; with 7-day refresh intervals nothing had fired yet. This is
  the intended way to exercise the watchdog today.
- **Health-scan error root cause:** the running Flask engine was OLD CODE (debug=false → no auto-reload;
  gap detector predated it), plus a real "connection refused" when the engine was down. Fixes: engine
  restarted with current code (MUST restart after editing scanner/*.py), ScannerClient::engineOnline()
  pre-flight (testing-env-gated) gives "start the engine with start.bat" instead of a bare failure,
  and the scraper show page pre-checks the engine before offering a scan.
- **Dashboard quick links:** Business Scraper / Auto-Scraper Queue / Websites & Health Scans buttons on
  the admin dashboard (the scraper was only findable in the sidebar before).
- **JSON-LD Service/Offer nodes** now feed `services` (hasOfferCatalog/makesOffer already did; bare
  Service/Offer graph nodes are now collected too).
- Verified live: modewjifoundation (about+address+gap), ariyafinergy (3 phones, about, email),
  aardvark (email, 2 gaps), scan 79/100 with mapped recommendation. Suite: 97 passed.
- NOTE: services stay empty for sites that genuinely expose no machine-readable service list — the
  engine extracts 3 shapes (line, list, JSON-LD) but never invents data.
- Operational reminders: restart the Flask engine after ANY scanner/*.py change; watchdog alerts fire
  on the SECOND scrape of a target (or use Re-scrape Now); schema.graphqls-style JS-rendered content
  still can't be read — that needs a headless browser (future work).

## Demo data cleanup (2026-09-24) — known-issue #4 closed
Deleted from the dev DB (owner-approved scope, transaction, backup first:
database/database.sqlite.bak-2026-09-24):
- Websites 1 (Demo Cafe), 2 (example.com), 227/228 (run-now-*.example.com) + their 16 scans,
  168 scan_results, 2 report rows + the 2 PDF files on disk
- Enquiries 1–8 (Jane/Bob/Carol/Dave/Erin/Fiona/Grace Test) + their 9 notifications
- 18 junk scraped businesses (Run Now Ltd / Cmd Ltd run-now & cmd-target URLs + "Example Domain")
- Scrape target 77 (example.com)
Kept: oweru.com self-scans, the localhost scan, tanzapages/odp.org, modewjifoundation,
aardvark/serenity/ariyafinergy, care plans + delivery commitments, all users. The 2 pending
queue jobs (AutoScanJob/PostScanJob for odp.org) were left alone — their records survive.
**STILL THERE:** 14 orphan scans with website_id NULL (test-*.example.com; ids 156,157,158,159,
164,169,190,195,200,205,210,215,220,225) — outside the approved scope, delete after confirming.
Suite verified after cleanup: 97 passed (385 assertions).

## Preliminary gap detection at scrape time (NEW 2026-09-23) — triage BEFORE the health scan
Staff can now decide whether a business is worth contacting immediately after the scrape, without
waiting for a full health scan:
- **Python engine (`business_scraper.py`):** new `GapDetector` runs on the home page ALREADY fetched by
  the scrape — zero extra requests, zero rate-limit/timeout impact. Deterministic checks: HTTPS, mobile
  viewport, contact/enquiry path (form + tel:/mailto:), page title, meta description, privacy policy,
  physical address, payment/booking path, copyright-year freshness. Output: `preliminary_gaps` =
  `[{check_name, gap, opportunity}, ...]` where **check_name REUSES the scanner's check names** so the
  existing recommendations mapping (check_name → Oweru service) attaches the exact service to sell with
  zero new config.
- **Laravel:** `scraped_businesses.preliminary_gaps` JSON column (migration 2026_09_23_000002), persisted
  by ScraperClient and refreshed on every re-scrape. Model helpers: `preliminaryGapCount()` and
  `outreachPriority()` (high = 3+ gaps, medium = 1–2, low = 0).
- **UI:** scraped-businesses INDEX gets a "Prelim. Gaps" column (badge colored by outreach priority,
  hover shows the gap list); SHOW page gets a "Preliminary Gaps & Opportunities" panel (gap + "→ Oweru
  opportunity" per row + priority badge) above the health-scan weaknesses panel.
- **Honest scoping:** this is quick triage from the scrape only — a site with 0 preliminary gaps can
  still have deep problems (speed, broken links, server issues) that only the full health scanner finds.
  The show page keeps both panels so staff see quick signals AND scan depth.
- Tests: 3 new in tests/Feature/ScraperTest.php (gaps stored + priority, show panel renders, index badge).
  Suite now at **97 passed**.

## Evidence pipeline: External API + AI interpretation (NEW 2026-09-23) — the 7-component platform is complete
Closes the roadmap: evidence → problems → what they MEAN → Oweru services. Components 1–3, 6–7 already
existed (scanner, scraper, structured findings, dashboard, PDF); this adds the two missing pieces:
- **Component 4 — External API (`app/Services/PageSpeedService.php`):** after every scan, PostScanJob asks
  Google PageSpeed Insights for real-world MOBILE performance (Lighthouse FCP/LCP/TBT/CLS + PSI score) —
  measurements our HTTP-based scanner cannot take. Stored as structured JSON on `scans.pagespeed`
  (migration 2026_09_23_000001). Field names are ours (`fcp_s`, `lcp_s`, `tbt_s`, `cls`), so a PSI API
  change only touches `PageSpeedService::extract()`. Log-only: a PSI outage never fails a scan.
- **Component 5 — AI API (`app/Services/AiInsightService.php`):** turns the structured findings into a
  plain-language executive summary, prioritised next actions and a ready-to-send pitch email, stored on
  `scans.ai_insight`. DIVISION OF RESPONSIBILITY (deliberate): what failed + evidence = scanner;
  business impact + which Oweru service to sell = the recommendations mapping table; AI only re-explains
  that evidence — it never invents problems or services. Uses OpenAI-compatible chat completions
  (`owers.ai.*`, any base_url works). **Deterministic fallback:** with no API key (or on API failure) the
  same summary/actions/email are built from the findings themselves — the pipeline is fully functional
  keyless; the dashboard badge shows which source produced each insight.
- **Orchestration:** both run inside PostScanJob (step 0, before PDF) so every scan path — public,
  manual, batch, auto-scraper — gets enriched. Scans with zero failed checks skip AI interpretation.
- **Surfaces:** admin scan page shows a "Real-World Mobile Performance" card (PSI score + 4 metrics) and
  a "What This Means (AI Insight)" card (summary, next actions, collapsible pitch-email draft); the PDF
  report gains compact PSI + "What This Means For Your Business" sections (still one page).
- **Config:** `owers.external.pagespeed.*` (OWERU_PAGESPEED_ENABLED/API_KEY — free tier works keyless,
  just rate-limited) and `owers.ai.*` (OWERU_AI_ENABLED/API_KEY/BASE_URL/MODEL). Both log-only and
  independently disable-able.
- Tests: tests/Feature/EvidencePipelineTest.php (9 — PSI capture/failure/disable/band, AI parse,
  keyless fallback, API-failure fallback, PostScanJob orchestration, no-failed-checks skip).
  Suite now at **94 passed**.

## Profile-based directory discovery (NEW 2026-09-23) — SPA directories now work
Classic discovery only fired when a scraped page linked out to ≥5 DISTINCT
EXTERNAL domains. Modern directories (tanzapages.com verified) are JavaScript
apps: their listing pages contain almost no server-side external links, so the
cascade silently died and the queue stayed frozen at the seed URLs.
- **Fix:** `business_scraper.py` now also detects INTERNAL PROFILE pages
  (`/company/{id}/{Name}`, `/business/…`, `/listing/…`, `/profile/…`,
  `/supplier/…`, `/vendor/…` — PROFILE_PATH_PATTERN) and treats ≥4 distinct
  profile URLs as a directory.
- It samples up to 5 profile pages per scrape (2s/host rate limit), reads each
  profile's HTML and extracts the business's REAL external website (first
  non-social external homepage link, robots + NON_BUSINESS_DOMAINS respected).
- These real websites flow into the existing `discovered_links` → Laravel
  `discoverTargets` → scrape → AutoScanJob chain unchanged.
- `discovery_limit` default lowered 20 → 5 (a drip per pass; each re-scrape
  cycle mines more of the directory). Tests that assert the cap mechanics set
  the limit explicitly.
- **Verified live on tanzapages.com:** 12 profile links found → 3 real
  businesses extracted (aardvark-expeditions.com, serenityafricasafaris.com,
  ariyafinergy.com) → scraped → auto health-scanned (scores 72/79/93, failed
  checks mapped to recommendations automatically).
- If BOTH shapes miss (no external-link cluster AND no profile links), the
  page is treated as a normal business site and seeds nothing.
- NOTE: profile fetching happens inside the scrape request — worst case adds
  ~5 × 2s+ to a directory scrape; the 180s HTTP timeout still fits.

## Auto health-scan (NEW 2026-09-23) — the loop is fully closed
The scraper no longer waits for a human to click "Run Health Scan": every
**newly scraped** business is automatically queued for a health scan
(AutoScanJob, source=auto_scraper). Scrape → weaknesses identified → mapped
Oweru recommendations attach by check_name — all with zero clicks.
- Triggered in ScraperClient only for `wasRecentlyCreated` businesses with
  status 'new' and no website_id (re-scrapes never re-trigger; human-scanned
  businesses are respected).
- Capped at OWERU_AUTO_SCAN_MAX_PER_RUN (default 5) per scrape:run pass so a
  big directory burst cannot overwhelm the scanner engine. Uncapped businesses
  are picked up on a later pass (they stay 'new').
- If the scan fails (engine offline, site down) the scan row stays 'failed',
  the business stays 'new', and a later scrape/auto-scan retries it.
- Requires the queue worker (queue:work) OR keep QUEUE_CONNECTION=sync so jobs
  run inline in dev.
- Disable with OWERU_AUTO_SCAN=false.
- Tests: tests/Feature/AutoScanTest.php (6 — scan+recommendation link, no
  re-scan of existing businesses, website_id guard, disable flag, per-run cap,
  failed-scan recovery).

## Scraper watchdog (NEW 2026-09-23) — watch + react, not just re-scrape
The 24/7 scraper is now a watchdog: every scheduled re-scrape is diffed against
the previous snapshot and meaningful changes are recorded + alerted.
- **Events** stored in `scrape_watch_events` (ScrapeWatchEvent model):
  `rating_drop` (hot; default threshold 0.5★, OWERU_WATCHDOG_RATING_DROP),
  `new_review` (hot when rated ≤3★, OWERU_WATCHDOG_BAD_REVIEW; otherwise
  noteworthy), `contact_changed` / `services_changed` (noteworthy, no alert),
  `site_down` (hot — recorded when a previously-scraped site fails a scheduled
  re-scrape) and `back_online` (auto-recorded on the next successful scrape).
- **Staff alerts** fire via NotificationService::alertStaff (new method,
  notification type `watchdog_alert` → all admins) for hot events only, with a
  per-business cooldown (default 24h, OWERU_WATCHDOG_COOLDOWN_HOURS) so a
  flapping site can't flood the inbox.
- **New reviews** are deduped with normalized text fingerprints stored on
  `scraped_businesses.seen_review_texts` (cap 50) — only genuinely new reviews
  create events.
- First scrape of a target establishes the baseline only (no events).
- Config block: `owers.scraper.watchdog.*`; disable entirely with
  OWERU_SCRAPER_WATCHDOG=false. Watchdog errors are log-only and can never
  break a scrape.
- **UI:** "Watchdog Activity" panel on the scraped-business show page
  (last 10 events, severity-colored badges, 🔔 marker when staff were alerted)
  plus a cross-business **Watchdog Activity feed on the admin dashboard**
  (last 8 events across ALL businesses, "N hot this week" badge when hot events
  fired in 7 days, rows link to each business page). Label/summary/badge
  rendering lives in ScrapeWatchEvent accessors (label/style/summary) so both
  views stay in sync.
- Directory harvesting (discovered_links → auto-queued targets) now supports
  TWO directory shapes — classic external-link listing pages AND profile-based
  (JavaScript) directories — and keeps feeding new prospects into the 24/7 queue.
- Tests: tests/Feature/ScrapeWatchdogTest.php (15 — rating drops, review
  dedupe, contact changes, baseline, site down/back online, cooldown,
  disable flag, panel render, dashboard feed).

## Scraping & receipts hardening (2026-09-22)
**Scraper:**
- Python engine now reads schema.org JSON-LD Organization/LocalBusiness blocks
  first (name/email/phone/address/services), regex fallbacks second; services/
  about regexes are line-anchored so nav junk no longer gets captured.
  Verified live against modewjifoundation.org (name/phone/address clean).
- Failed scrape targets are retried after OWERU_SCRAPER_RETRY_HOURS (default 6)
  instead of waiting the full 7-day refresh interval (ScrapeTarget::due).
- Re-scrapes no longer reset a business's status to 'new' once it is a
  lead/scanned, and never blank previously-scraped fields with empty values
  (ScraperClient upsert).
- Flask now runs threaded=True; ScraperClient HTTP timeout 120s→180s (out-waits
  the engine's worst case ~140s).

**Receipts (the real fix):**
- PesaPal's browser callback often reports PENDING while money is still moving;
  the old code marked that payment FAILED and the receipt never fired. PENDING
  now keeps the record pending; only FAILED/CANCELED/INVALID closes it.
- All settlement paths (callback, IPN, reconciliation) funnel through
  InvoiceSettlementService::settle() — a cache lock + payments.settled_at
  watermark means the receipt is generated exactly once even when IPN and
  callback race each other.
- NEW `payments:reconcile` command, scheduled every 10 min: settles completed
  payments whose callback/IPN never landed (receipt fires then), re-asks
  PesaPal about payments pending >1h, closes FAILED ones, warns about payments
  pending >24h. `--dry-run` for inspection.
- PesaPal IPN id is no longer cached as null for 24h when registration fails;
  the next checkout retries registration.
- Migration: add_settled_at_to_payments_table.

**Services images:** all 5 public/images/services/*.jpg replaced with new
themed stock photos (in-place filenames, so home + services pages both update;
no code changes).

## Auto-scraper fix (2026-09-21) — why it fetched nothing
Three stacked root causes, all fixed:
1. **DB fallback only covered web requests** — `scrape:run` (console/scheduler) crashed when MySQL was down. Now `bootstrap/app.php` `booted()` falls back to SQLite for web, console AND scheduler when `DB_FALLBACK_TO_SQLITE=true`.
2. **`start.bat` never started the scheduler** — `schedule:work` is what fires `scrape:run` every 5 min. Now service [3/4].
3. **`config/database.php` fed `DB_DATABASE=oweru_tech_solutions` (a MySQL name) to the sqlite driver**, which silently created an empty throwaway DB. SQLite now only honors `:memory:` or `*.sqlite` values; anything else falls back to `database/database.sqlite`.
Also: tests now run on isolated `:memory:` DB with `RefreshDatabase` + seeders (they were silently reading/writing the dev DB — source of the junk `https://a`-style targets, now deleted). NOTE: tests no longer depend on dev data; fixture-dependent tests create their own records.

## What this project is
Laravel 11 + Python Flask website scanner. Builds 1–3 of the brief are COMPLETE and verified end-to-end:
1. Service packages page (TZS/USD, admin-editable)
2. Enquiry + qualification pipeline (New → Qualified → diagnostic_paid → proposal_sent → won/lost)
3. Site Health Scanner (23 checks, 8 areas, 100 pts) + one-page PDF reports + recommendations engine

## Scraper V1 (NEW 2026-09-18) — public business info scraping
Staff page at /admin/scraped-businesses: enter a public URL → Python engine extracts
business name, public email, phone, address, services, about → saved to scraped_businesses.
Per-row actions: [View] [Run Health Scan] (creates Website + runs existing scanner) and
[Add to Leads] (creates enquiry with source=outreach). Engine: scanner/business_scraper.py,
endpoint POST /api/scraper/scrape on the same Flask service (port 5000) — no new deps
(stdlib HTMLParser). Robots.txt respected, 2s rate limit, honest UA, no raw HTML stored.
Tests: tests/Feature/ScraperTest.php (7). Verified live against modewjifoundation.org.

Plus: queued batch scanning ("⚡ Scan All"), enquiry email notifications, 3-day stage reminders.

## Invoicing V1 (NEW 2026-09-21) — 50% deposit + automatic receipts
Invoices are created automatically when an enquiry reaches **proposal_sent**
(EnquiryObserver → InvoiceService), or manually at /admin/invoices
[New Invoice]. Default terms: **50% deposit before work starts**, balance on
completion (config: OWERU_DEPOSIT_PERCENT, default 50; numbering OWU-INV-YYYY-####).
Checkout types added to the public PesaPal flow: `invoice-deposit` and
`invoice-full` (pay remaining balance). Completed payments settle into the
invoice (draft → issued → part_paid → paid) and the moment it is fully paid a
**receipt PDF is generated automatically** and emailed with the exact payment
list (date, reference, method, type, amount). Receipt download links are
token-guarded (route invoice.receipt). Views: pdf/invoice, pdf/receipt,
emails/invoice, emails/receipt. Tests: tests/Feature/InvoiceTest.php (9).

**Overdue-deposit reminders (NEW 2026-09-21):** scheduled daily at 10:00
(`invoices:send-overdue-reminders` in routes/console.php). Customers whose
deposit passed its due date get a friendly email with the outstanding amount
and payment link — repeated every OWERU_REMINDER_INTERVAL_DAYS (default 3),
max OWERU_MAX_REMINDERS (default 4) per invoice, never for paid/cancelled or
deposit-paid invoices. **Staff are alerted too** (NotificationService
::overdueDeposit, type=overdue_deposit → admins + enquiry owner, with the
customer's phone number and a call-to-action so the team can follow up by
phone — same cadence as the customer email, audit-recorded in notifications).
Admin sees an "⚠ Nd overdue" badge on the invoices index and reminder history
on the detail page. Mailable: DepositReminderMail.
Tests: tests/Feature/InvoiceReminderTest.php (11).

## Scraper V2 (NEW 2026-09-21) — 24/7 auto-scraping + reviews/ratings/weaknesses
- **24/7 queue:** /admin/scrape-targets — paste URLs (bulk, one per line) and
  the scheduler scrapes them automatically, re-scraping every N days (default 7)
  to keep data fresh. Command: `php artisan scrape:run` (also `--add="url1 url2"`,
  `--stats`), scheduled every 5 min with withoutOverlapping — REQUIRES
  `php artisan schedule:work` running (add as 4th terminal). Per-target
  pause/resume/run-now/remove. Tests: tests/Feature/ScrapeTargetTest.php (8).
- **Reviews & ratings:** business_scraper.py now extracts testimonials, star
  ratings and review text from the target's OWN public pages only
  (schema.org JSON-LD, microdata, on-page blockquotes). No third-party review
  platforms are contacted. Stored on scraped_businesses (rating_avg, rating_count,
  reviews JSON) and shown on the scraped-business show page.
- **Weaknesses & challenges:** scraped-business show page now lists the latest
  health scan's failed checks with finding, impact and the mapped Oweru
  recommendation, next to the reviews — so a scraped business's pain points are
  visible at a glance for outreach.

## GitHub
Repo: https://github.com/witnessamson047/oweru-tech-solutions (private, branch `main`)
Push workflow: `git add .` → `git commit -m "message"` → `git push`

## How to run everything (4 terminals)
**Easiest: double-click `start.bat`** — launches all four services, and the
queue worker now runs through `queue-worker.bat`, an auto-restarting wrapper
(a crashed worker restarts in 3s instead of silently piling up stalled jobs —
this bit us on 2026-09-23 when a manually-started worker was left off and
33 jobs stalled). Manual equivalent:
```bash
php artisan serve                                # app → http://127.0.0.1:8000
C:\python312\python.exe scanner\scanner.py       # scanner+scraper engine → port 5000 (REQUIRED)
queue-worker.bat                                 # queue:work loop (auto-restarts; plain: php artisan queue:work)
php artisan schedule:work                        # 24/7 auto-scraper + reminders (NEW)
```
Login: admin@oweru.co.tz / change-me-now   (manager@oweru.co.tz / change-me-too)

## Environment gotchas (important!)
- Windows system env has MAIL_MAILER=smtp / MAIL_HOST=mailpit baked in — it OVERRIDES .env.
  For local email testing start servers with: `MAIL_MAILER=log MAIL_HOST=localhost php artisan serve`
  (emails then appear in storage/logs/laravel.log)
- Windows system env ALSO pins APP_ENV=local, which leaks into $_SERVER and defeats phpunit's <env> settings —
  tests used to get 419 CSRF errors. Fixed by tests/bootstrap.php (forces APP_ENV=testing pre-boot, set as
  phpunit bootstrap). If POST tests ever 419 again, check tests/bootstrap.php is still wired in phpunit.xml.dist.
- Python 3.12 is at C:\python312 (not on PATH)
- Local DB is SQLite at database/database.sqlite (MySQL-ready via .env)

## Brand re-theme (NEW 2026-09-21) — deep navy + vibrant blue
The old black/gold scheme is replaced with a navy/blue identity (inspired by a
Webfosys Ventures reference poster). HOW: Tailwind v4 `@theme` remap in
resources/css/app.css maps every `yellow-*` utility to the blue ramp, so all
34 blade files re-themed without edits. CSS vars (--gold→blue etc.), buttons
(blue gradients), navy navbar/footer, navy scanner hero + login, home headline
in Webfosys style ("DIGITAL SOLUTIONS THAT DRIVE REAL GROWTH"), uppercase
section titles. Contrast pass: every `bg-yellow-* text-black` combo rewritten
to `bg-blue-600 text-white` (or blue-200/navy). PDFs + emails: hardcoded gold
hexes (#D4AF37/#eab308) swapped to blue (#3b82f6/#2563eb) on navy (#0a1730).
Verified: npm run build clean, --color-yellow-600 resolves to #2563eb in the
built CSS, 54/54 tests pass. To tweak the palette later, edit ONLY the @theme
block in resources/css/app.css.

## Known issues / next steps (in priority order)
1. ~~MISSING VIEWS~~ FIXED 2026-09-11: admin/websites/create.blade.php and edit.blade.php built (were 500ing). Feature tests added (tests/Feature/AdminWebsitesTest.php, 5 tests).
2. ~~Doc gaps from the audit~~ CLOSED 2026-09-24: public scan shows 3 findings (doc §11);
   weights are admin-configurable via /admin/scanner-checks; re-scan comparison (improved/declined)
   exists on the scan page. Remaining nuance: scanner.py has DEFAULT weights that are overridden by
   the DB payload — the scanner_checks table is the single source of truth in practice.
3. User #1 admin@oweru.com was deleted from DB intentionally.
4. ~~Test data exists~~ CLEANED 2026-09-24 (see "Demo data cleanup" above). Remaining: 14 orphan
   test-*.example.com scans with website_id NULL — confirm then delete.
5. Discovery module: Steps 1-3+6 DONE, verified live end-to-end through the admin UI (see top
   section). The crawler plan is COMPLETE. Operational notes: public Overpass must be treated
   gently (one run at a time); the queued 96 Dar targets process at the normal scrape:run cadence
   (batch 10); remember the Windows DNS quirk — discovery runs MUST go through the queue worker,
   never expect the serve lineage to reach the internet.

## What was verified working today (2026-09-22)
- Python scraper CLI live against example.com + modewjifoundation.org (clean extraction, no nav junk)
- JSON-LD business extraction verified against synthetic LocalBusiness markup
- **Full receipt pipeline verified end-to-end on the dev DB** (simulated-PesaPal script, since no sandbox keys):
  enquiry → auto-invoice (OWU-INV-2026-0001, TZS 1.5M issued) → deposit settled via reconcile → part_paid →
  balance completed with callback+IPN both missed → payments:reconcile settled it → paid → receipt PDF
  (6.9 KB) auto-generated → ReceiptMail logged to mailer → public token-guarded download HTTP 200,
  invalid token HTTP 403. To repeat with live PesaPal: set PESAPAL_CONSUMER_KEY/SECRET + PESAPAL_IPN_URL,
  pay at /pay/invoice-deposit/{id}, then confirm storage/app/receipts/ + invoices.receipt_generated_at.
- payments:reconcile covered by 6 new tests (missed settlement, stale pending→completed with receipt, pending→failed, fresh pending untouched, double-settle idempotent, legacy applyPayment)
- Scrape retry + lead-status preservation covered by 2 new tests
- Scraper watchdog: 15 new tests + auto-scan 6 new tests; evidence pipeline (PSI + AI insight):
  9 new tests; preliminary gaps 3 new tests; suite now at **97 passed** (2026-09-23)

## Verified previously (2026-09-21)
- New migrations ran clean on SQLite (invoices, payments/scraped_businesses columns, scrape_targets)
- Review extraction verified against sample JSON-LD + testimonial HTML (rating_avg 4.6, 2 reviews parsed)
- Receipt PDF auto-generates on full settlement and attaches to email (covered by tests)
- Tests: 43 passed (26 pre-existing + 17 new: InvoiceTest 9, ScrapeTargetTest 8)
- (2026-09-22 note: suite now at 62 passed)
- Fixed: PesapalService no longer fatals when PESAPAL_CONSUMER_KEY is unset (was 500ing checkout pages in test env)
