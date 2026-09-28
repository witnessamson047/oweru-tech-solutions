# 📚 OWERU TECH SOLUTIONS — COMPLETE SYSTEM STUDY GUIDE

A plain-language walkthrough of how this project is built, folder by folder,
flow by flow, with diagrams. Read top to bottom and you will understand every
moving part of your own system.

---

## 1. WHAT THIS SYSTEM IS (the big picture)

Oweru Tech Solutions sells website services. This application is the company's
own digital arm and does four jobs at once:

1. **Marketing** — a public website showing packages, care plans and pricing
2. **Lead capture** — enquiry forms, a free public website scanner, and a
   business-info scraper that finds leads automatically
3. **Operations** — an admin panel to run the scanner, manage leads through a
   sales pipeline, and track every website scan ever made
4. **Money** — invoicing with 50% deposits, PesaPal mobile-money checkout,
   automatic receipts, and overdue-payment reminders

```
                    ┌──────────────────────────────────────────┐
                    │            OWERU TECH SOLUTIONS          │
                    └──────────────────────────────────────────┘
                                     │
        ┌────────────────┬───────────┼───────────────┬────────────────┐
        ▼                ▼           ▼               ▼                ▼
   PUBLIC SITE      SCANNER      LEAD PIPELINE   INVOICING      24/7 SCRAPERS
   (marketing)      (health      (sales CRM)     (PesaPal +     (lead hunting)
   packages,        scoring +    enquiry stages  auto receipts  target queue,
   enquiry form     reports)     owner, notes    50% deposit)   reviews, pains
        │                │           │               │                │
        └────────────────┴─────┬─────┴───────────────┴────────────────┘
                               ▼
                    ┌─────────────────────┐
                    │      ADMIN PANEL    │
                    │   /admin (staff)    │
                    └─────────────────────┘
```

**Stack:** Laravel 11 (PHP, the web app + API) + Python/Flask (the scanner and
scraper engine) + SQLite locally (MySQL-ready) + Tailwind CSS v4 + PesaPal
(Tanzanian mobile money) + DomPDF (PDF invoices/receipts/reports).

---

## 2. FOLDER STRUCTURE (what lives where)

```
oweru-tech-solutions/
│
├── app/                          ← the brain (PHP)
│   ├── Console/Commands/         ← long-running jobs (artisan commands)
│   │   ├── ScrapeRunCommand.php       scrape:run      — processes the scrape queue
│   │   ├── ReconcilePaymentsCommand   payments:reconcile — fixes missed payments
│   │   ├── SendInvoiceReminders.php   invoices:send-overdue-reminders
│   │   ├── SendStageReminders.php     enquiries:send-stage-reminders
│   │   └── DiscoveryRunCommand.php    discovery:run   — OSM website discovery probe
│   │
│   ├── Http/Controllers/
│   │   ├── (root)                ← PUBLIC pages: HomeController, PackageController,
│   │   │                            EnquiryController, ScannerController,
│   │   │                            PaymentController (PesaPal flow lives here)
│   │   ├── Admin/                ← STAFF pages: 16 controllers (dashboard,
│   │   │                            pipeline, invoices, scrape targets, …)
│   │   └── Api/                  ← JSON endpoints (ScannerApiController)
│   │
│   ├── Jobs/                     ← queued background work
│   │   ├── ScanWebsiteJob.php         batch "Scan All" runs one scan per job
│   │   ├── PostScanJob.php            after each scan: PSI + AI insight, PDF, email
│   │   ├── AutoScanJob.php            health-scans each newly scraped business
│   │   ├── ScrapeTargetJob.php        scrapes one queue target (watchdog)
│   │   └── DiscoveryJob.php           queued OSM discovery run (DNS-safe)
│   │
│   ├── Mail/                     ← email templates (Mailables)
│   │   ├── InvoiceMail.php            the invoice PDF emailed to customer
│   │   ├── ReceiptMail.php            receipt PDF attached, sent when fully paid
│   │   ├── DepositReminderMail.php    "your deposit is overdue" nudge
│   │   └── ScanResultsMail.php        scan score + findings emailed
│   │
│   ├── Models/                   ← 21 Eloquent models = 21 database tables
│   │   ├── Enquiry.php            a lead (the heart of the pipeline)
│   │   ├── Website.php            a site we track (exclusion list lives here)
│   │   ├── Scan.php + ScanResult  one health scan + its 23 check results
│   │   ├── ScannerCheck.php       admin-editable check definitions/weights
│   │   ├── Recommendation.php     maps a failed check → sales pitch
│   │   ├── Report.php             generated PDF record
│   │   ├── ServiceLine.php        grouping (software dev / web / CRM / network)
│   │   ├── ServicePackage.php     sellable packages (TZS/USD)
│   │   ├── CarePlan.php           monthly subscriptions
│   │   ├── Enquiry → Invoice.php  OWU-INV-YYYY-####, deposit terms, receipt
│   │   ├── Payment.php            every PesaPal attempt (pending→completed/failed)
│   │   ├── Notification.php       audit log of every staff alert email
│   │   ├── ScrapedBusiness.php    a business the scraper extracted
│   │   ├── ScrapeTarget.php       URL queue for the 24/7 scraper
│   │   ├── ScrapeWatchEvent.php   watchdog: rating drops, contact changes…
│   │   ├── DiscoveryRun.php       one OSM website-discovery run
│   │   ├── DiscoveryLead.php      business found with NO website (outreach)
│   │   └── User.php               staff accounts (role: admin)
│   │
│   ├── Observers/
│   │   └── EnquiryObserver.php    ★ MAGIC: when an enquiry reaches
│   │                               'proposal_sent' an invoice is auto-created
│   │                               and emailed — no button needed
│   │
│   └── Services/                 ← business logic (controllers stay thin)
│       ├── ScannerClient.php          talks to Python scanner, saves results
│       ├── ScraperClient.php          talks to Python scraper, saves businesses
│       ├── InvoiceService.php         create/issue invoices, numbers, PDFs
│       ├── InvoiceSettlementService   ★ race-safe "apply payment + receipt once"
│       ├── PesapalService.php         PesaPal API (token, orders, IPN, status)
│       ├── NotificationService.php    staff alerts (email + DB audit trail)
│       ├── ScrapeWatchdogService      diffs re-scrapes, fires change alerts
│       ├── PageSpeedService.php       Google PSI mobile metrics per scan
│       ├── AiInsightService.php       plain-language insight from findings
│       └── DiscoveryService.php       OSM probe shell-out (website discovery)
│
├── scanner/                      ← the PYTHON half (independent service)
│   ├── scanner.py                ← Flask app on port 5000
│   │                               • /api/scanner/scan  — 23 health checks
│   │                               • /api/scraper/scrape — business extraction
│   │                               • /health            — "am I alive?"
│   ├── business_scraper.py       ← polite scraper engine (robots.txt, 2s delay,
│   │                               Playwright headless render for SPA sites)
│   └── osm_discovery.py          ← OpenStreetMap website-discovery probe
│                                   (standalone; Laravel shells out to it)
│
├── database/migrations/          ← 30 migrations = the database schema history
│
├── resources/views/              ← Blade templates (the HTML)
│   ├── pages/                    ← public: home, packages, enquiry
│   ├── scanner/                  ← public scanner page
│   ├── payment/                  ← checkout + payment status pages
│   ├── admin/                    ← 16 admin sections (dashboard, invoices,
│   │                              discovery, scrape targets…)
│   ├── emails/                   ← email HTML (invoice, receipt, reminders)
│   └── pdf/                      ← PDF layouts (invoice, receipt, scan report)
│
├── routes/
│   ├── web.php                   ← every URL in the app (60 routes)
│   └── console.php               ← the scheduler (what runs every 5 min / daily)
│
├── config/
│   ├── owers.php                 ← YOUR business config (deposit %, reminder
│   │                                cadence, scraper cadence)
│   └── services.php              ← PesaPal + scanner service URLs/keys
│
├── public/images/                ← photos (services + hero)
├── storage/app/receipts/         ← generated receipt PDFs land here
├── storage/app/reports/          ← generated scan-report PDFs land here
└── start.bat                     ← double-click = starts all 4 services
                                   (+ opens the site in your browser)
```

---

## 3. THE TWO PROCESSES AND HOW THEY TALK (the core integration)

The most important architectural fact: **PHP and Python are two separate
programs** that only speak HTTP to each other. Laravel never imports Python
code — it makes HTTP calls to `http://localhost:5000`.

```
┌─────────────────────────┐                        ┌──────────────────────────┐
│   LARAVEL (PHP) :8000   │                        │  FLASK ENGINE (PY) :5000 │
│                         │    POST /api/scanner/  │                          │
│  ScannerClient ─────────┼──▶ scan {url, checks} ─▶│  WebsiteScanner          │
│                         │◀── {score, results} ───│  • 23 checks, 8 areas    │
│  ScraperClient ─────────┼──▶ POST /api/scraper/  │  • 100 points total      │
│                         │    scrape {url}        │                          │
│                         │◀── {name,email,phone, ─│  BusinessScraper         │
│                         │      address,reviews}  │  • robots.txt respected  │
│                         │                        │  • 2s rate limit         │
│                         │    GET /health (3s)    │  • JSON-LD + regex       │
│  DashboardController ───┼───────────────────────▶│                          │
└─────────────────────────┘                        └──────────────────────────┘
        │                                                   ▲
        │ saves to SQLite/MySQL                             │ actually visits
        ▼                                                   │ the target site
┌──────────────────┐                                        │
│  DATABASE        │                          ┌─────────────┴───────────┐
│  18 tables       │                          │  The internet           │
│  websites, scans,│                          │  (target websites)      │
│  enquiries, ...  │                          └─────────────────────────┘
└──────────────────┘
```

Why split it? PHP is great at forms, databases and billing. Python is great at
HTTP parsing and text extraction. Each does what it's best at; the HTTP boundary
means you can restart or crash one without taking the other down. Laravel also
pre-flights the engine (ScannerClient::engineOnline) and tells staff to "start
the engine with start.bat" instead of failing with a bare connection error.

### API key handshake
Both sides share `SCANNER_API_KEY` (env var). Laravel sends it in the
`X-API-Key` header; Flask checks it and returns 401 on mismatch. Empty key =
open access (fine on localhost).

---

## 4. FLOW 1 — PUBLIC SCANNER (Build 3, the lead magnet)

A visitor checks their own website's health. This is the top of your funnel.

```
 Visitor                Laravel :8000              Flask :5000            DB
    │                        │                          │                 │
    │ GET /website-check     │                          │                 │
    ├───────────────────────▶│  (scanner/index.blade)   │                 │
    │ types URL (free scan)  │                          │                 │
    │ (report on request)    │                          │                 │
    │                        │  ScannerClient::runScan  │                 │
    │                        ├─ POST /api/scanner/scan ▶│ visits the site │
    │                        │                          │ runs 23 checks  │
    │                        │◀─ {score, 23 results} ───┤                 │
    │                        │  Scan(status=completed) ──────────────────▶│
    │                        │  ScanResult × 23        │                 │
    │                        │  PostScanJob (queued)   │                 │
    │                        │   ├─ PSI mobile metrics │                 │
    │                        │   ├─ AI insight + pitch │                 │
    │                        │   ├─ PDF report made    │                 │
    │                        │   ├─ ScanResultsMail    │                 │
    │                        │   └─ staff notified     │                 │
    │ sees score 0–100       │◀────────────────────────┴─────────────────│
    │ + top 3 problems       │                          │                 │
    │                        │                          │                 │
    │ wants the full report  │                          │                 │
    ├─ report-request form ─▶│  Enquiry created! ───────────────────────▶│
    │ (name,email,consent)   │  (source=scanner, stage=new)               │
    │                        │  NotificationService → staff email         │
```

Key detail: every public scan **auto-creates an enquiry row** (source
`scanner`). The scanner is not just a toy — it is a lead factory. Failed checks
are mapped through the `recommendations` table to a sales pitch (e.g. "No SSL →
recommend our security package").

---

## 5. FLOW 2 — ENQUIRY PIPELINE (Build 2, the sales CRM)

Leads from all sources (form, scanner, scraper) flow through fixed stages:

```
   new ──▶ qualified ──▶ diagnostic_paid ──▶ proposal_sent ──▶ won / lost
     │          │               │                  │
     │          │               │                  └── ★ EnquiryObserver fires:
     │          │               │                      Invoice auto-created + issued
     │          │               │                      + emailed (50% deposit terms)
     │          │               │
     │          │               └─ customer paid TZS 10,000 for the full report
     │          │                  (PesaPal payment auto-advances the stage)
     │          └─ staff judged the lead real (owner assigned, notes kept)
     └─ fresh lead from enquiry form / scanner / scraper
```

- Staff move stages manually at `/admin/enquiries` (dropdown) or see the whole
  board at `/admin/pipeline` (kanban view).
- `SendStageReminders` (daily 09:00) emails staff about anything stuck 3+
  working days — no lead rots silently.
- `NotificationService` records every staff email in the `notifications` table
  (audit trail), so you can always see who was told what.

---

## 6. FLOW 3 — INVOICING + PESAPAL + AUTOMATIC RECEIPTS

This is the money pipeline, and the most carefully engineered part.

### 6a. Invoice creation and terms

```
 Enquiry hits 'proposal_sent'
        │
        ▼
 EnquiryObserver::updated()
        │
        ▼
 InvoiceService::createForEnquiry()
   • number: OWU-INV-2026-0001 (sequential, lock-protected)
   • total:  from package price, else TZS 1,500,000 default
   • deposit: 50% (config OWERU_DEPOSIT_PERCENT)
   • status: draft
        │
        ▼
 InvoiceService::issue()
   • status: issued, due_at = +7 days
   • InvoiceMail → customer (PDF attached)
```

### 6b. Checkout and payment through PesaPal

```
Customer                 Laravel                    PesaPal
   │  /pay/invoice-deposit/5  │                         │
   ├─────────────────────────▶│ Payment row (pending)   │
   │                          ├─ submit order ─────────▶│
   │◀── redirect to PesaPal ──┤                         │
   │ pays with mobile money ──────────────────────────▶ │
   │                          │◀─ browser callback ─────┤  (channel 1)
   │                          │◀─ server IPN ───────────┤  (channel 2)
   │                          │  GetTransactionStatus   │
   │                          │  status=COMPLETED?      │
   │                          ▼                         │
   │                 ┌──────────────────────┐           │
   │                 │ InvoiceSettlement    │           │
   │                 │ Service::settle()    │           │
   │                 │  • cache lock (race  │           │
   │                 │    safe vs IPN)      │           │
   │                 │  • payments.settled_at (exactly-once watermark)
   │                 └──────────┬───────────┘           │
   │                            ▼                       │
   │        amount_paid >= total ?                     │
   │           ├─ no  → invoice = part_paid            │
   │           └─ yes → invoice = paid                 │
   │                    │                              │
   │                    ▼                              │
   │         InvoiceService::generateReceipt()         │
   │           • PDF from pdf/receipt.blade            │
   │           • saved to storage/app/receipts/        │
   │           • ReceiptMail + PDF attachment          │
   │◀── receipt email with exact payment list ─────────┤
   │     (date, reference, method, amount)             │
```

### 6c. The safety net (what my 2026-09-22 hardening added)

Real PesaPal has three failure modes; each now has a fix:

| Failure mode | Before | Now |
|---|---|---|
| Callback says PENDING (money still moving) | marked **failed** → receipt never fired | PENDING keeps record pending; only FAILED/CANCELED/INVALID closes it |
| Both callback AND IPN missed | money arrived, invoice untouched forever | `payments:reconcile` (every 10 min) settles completed-but-unsettled payments |
| IPN + callback race each other | double receipt / double transition | cache lock + `settled_at` watermark = exactly once |
| IPN registration failed once | null cached 24h → silence | null not cached; next checkout retries |

Also: stale pending payments older than 1h are re-checked with PesaPal; >24h
triggers a staff warning. Deposits overdue past `due_at` trigger a customer
email every 3 days (max 4) **plus** a staff alert with the customer's phone
number so the team can call.

---

## 7. FLOW 4 — THE 24/7 SCRAPER (lead hunting while you sleep)

Scraper V1 is manual (paste a URL at `/admin/scraped-businesses`). Scraper V2
is the automated queue:

```
STAFF (you)                     SCHEDULER (every 5 min)              PYTHON :5000
   │                                    │                                │
   │ paste 50 URLs at                   │  php artisan scrape:run        │
   │ /admin/scrape-targets              ├────────────────────────────────▶│
   │                                    │  fetch next due batch           │
   │                                    │  (≤10, oldest first)            │
   │                                    │        POST /api/scraper/scrape │
   │                                    ├────────────────────────────────▶│
   │                                    │                                 │ ① robots.txt check
   │                                    │                                 │ ② fetch home + about/
   │                                    │                                 │    contact/services
   │                                    │                                 │ ③ extract: name, email,
   │                                    │                                 │    phone, address,
   │                                    │                                 │    services, about,
   │                                    │◀─ structured JSON ──────────────┤    reviews, ratings
   │                                    │                                 │    (JSON-LD + regex)
   │                                    │  ScrapeTarget:                  │
   │                                    │   last_scraped_at=now           │
   │                                    │   scrape_count++                │
   │                                    │   last_error=null               │
   │                                    │  ScrapedBusiness: upsert        │
   │                                    │   (status preserved if lead!)   │
   │                                    │                                 │
   │ see it at /admin/scraped-businesses│                                 │
   │  [Run Health Scan] → full scan of that business (Flow 1, score!)    │
   │  [Add to Leads]    → Enquiry(source=outreach, stage=new)            │
   │                                    │                                 │
   │ show page also lists:              │                                 │
   │  • reviews & star ratings          │                                 │
   │  • latest scan's failed checks     │  ← the pain points you sell to  │
```

Re-scraping cadence: every 7 days (config) keeps data fresh; a failed target is
retried after 6 hours instead of waiting a week. The scheduler also handles
`payments:reconcile` (every 10 min), stage reminders (09:00) and overdue
deposit reminders (10:00). All of this needs `php artisan schedule:work`
running — that's the Scheduler window in start.bat.

Two automatic extras on top of the queue:

- **Auto health-scan** — every NEWLY scraped business is queued for a full
  health scan automatically (AutoScanJob, capped at 5 per pass). Scrape →
  weaknesses → mapped recommendations, all with zero clicks. Disable with
  OWERU_AUTO_SCAN=false.
- **Watchdog** — each scheduled re-scrape is diffed against the previous
  snapshot; rating drops, new bad reviews, contact/service changes, site
  down / back online are recorded in `scrape_watch_events` and hot events
  email the staff (with a 24h per-business cooldown). Disable with
  OWERU_SCRAPER_WATCHDOG=false.

After every scan, PostScanJob also enriches it: Google PageSpeed Insights
mobile metrics (`scans.pagespeed`) and an AI plain-language insight with a
ready-to-send pitch email (`scans.ai_insight`). Both are log-only, work
keyless with deterministic fallbacks, and never fail a scan.

### 7b. WEBSITE DISCOVERY (OSM — staff never type a URL)

Staff pick a city + category at `/admin/discovery`; Laravel shells out to
`scanner/osm_discovery.py` (OpenStreetMap Nominatim + Overpass — deliberately
NOT part of the Flask engine, so it works even when the engine is down). The
run is QUEUED (DiscoveryJob on the queue worker — required, because the long-
lived `artisan serve` process on Windows can't resolve DNS) and produces:

- businesses WITH a website → queued into `scrape_targets` (deduped by host,
  tagged with `discovery_run_id`) → the normal scrape → auto-scan pipeline
- businesses with NO website → `discovery_leads`, the "we'll build you one"
  outreach list at `/admin/discovery/leads` (map links, mark-contacted)

Public Overpass is shared infrastructure: one query per run, never loop it.
There is also a manual Re-scrape button on each scraped business — watchdog
events need a second scrape to diff against, so that's the intended way to
exercise the watchdog today.

---

## 8. DATABASE MAP (how the 21 tables relate)

```
 users (staff)
   │
   └── owner_id ──────────────┐
                              ▼
 service_lines ──▶ service_packages ──┐        ENQUIRIES (leads)
                      care_plans ─────┤         │  │
                                      │         │  └── invoices ──▶ payments
 package_exclusions                   │         │         (settled_at)
 delivery_commitments                 │         │
                                      │         │
 websites ──▶ scans ──▶ scan_results ◀┘         │
   │            │         │                      │
   │            │         └──▶ recommendations   │
   │            │            (check→pitch map)    │
   │            └──▶ reports (PDFs)              │
   └── enquiry_id ───────────────────────────────┘
          (scanner-created enquiries link back)

 scrapers (separate cluster):
 scrape_targets ──(url)──▶ scraped_businesses ──▶ website_id / enquiry_id
 │                        (reviews, ratings,
 │                         weaknesses JSON)
 └──▶ scrape_watch_events (watchdog diffs between re-scrapes)

 discovery (OSM cluster):
 discovery_runs ──▶ scrape_targets (discovery_run_id provenance)
       └──────────▶ discovery_leads (businesses with NO website → outreach)

 notifications (audit log of every staff email, optional enquiry_id)
```

Important columns to know:
- `websites.exclusion_status` — 'excluded' sites can never be scanned (opt-out)
- `scans.source` — 'public' | 'admin' | 'scraper' tells you where it came from
- `payments.kind` — 0 direct / 1 deposit / 2 balance / 3 full settlement
- `payments.settled_at` — the exactly-once receipt watermark (2026-09-22)
- `invoices.status` — 0 draft / 1 issued / 2 part_paid / 3 paid / 4 cancelled
- `scanner_checks.weight/enabled` — admin-tunable scoring (sent to Python per scan)

---

## 9. THE SCAN ITSELF (what the 23 checks do)

Areas and example checks (100 points total; weights editable at
`/admin/scanner-checks` and passed to Python on every scan):

```
 SECURITY (3)     MOBILE (3)       SPEED (3)         FUNCTION (3)
 • SSL valid      • responsive     • loads < 3s      • contact form
 • expiry >30d    • readable text  • page < 2 MB     • tappable phone/email
 • no insecure    • tap-friendly   • images sized    • no broken links
   items (mixed
   content)

 FINDABILITY (4)  TRUST (4)        FRESHNESS (2)     COMMERCE (1)
 • page title     • company name   • content < 12mo  • payment/booking
 • meta desc      • address        • copyright year    path present
 • name search    • privacy policy
 • GBP points     • terms of service
                       │
                       ▼
 each result: { check_name, area, passed, points, evidence,
                finding_text, consequence }
                       │
                       ▼
 score = Σ points → band: Strong 80+ / Adequate 60–79 / Weak 40–59 /
                          Critical <40
```

Every failed check gets `evidence` (what was found), `finding_text` (what's
wrong) and `consequence` (why it matters) — that's what makes the PDF report
sell for you.

---

## 10. HOW TO RUN EVERYTHING (4 terminals, or just start.bat)

```
php artisan serve                                 # app       → http://127.0.0.1:8000
C:\python312\python.exe scanner\scanner.py        # engine    → port 5000 (REQUIRED)
queue-worker.bat                                  # queue:work loop, auto-restarts
                                                  # (batch scans, PDFs, mail, auto-scan)
php artisan schedule:work                         # 24/7: scraper + reconcile + reminders
```

`start.bat` starts all four in separate windows and opens the site in your
browser. The queue worker runs through `queue-worker.bat`, an auto-restarting
wrapper (a crashed worker restarts in 3s instead of silently piling up jobs).

- Login: admin@oweru.co.tz / change-me-now (manager@oweru.co.tz / change-me-too)
- DB: SQLite at database/database.sqlite (auto-falls-back from MySQL when down)
- Emails locally: `MAIL_MAILER=log php artisan serve` → they appear in
  storage/logs/laravel.log (Windows system env can override .env — see below)

### Tests (110 passing, 453 assertions)
```
php artisan test
```
Run on an isolated in-memory SQLite DB — they can never touch your dev data.
Coverage: invoices (9), reminders (11), scraper (7), scrape targets (10),
reconciliation (6), admin websites (5), discovery (11), watchdog (15),
auto-scan (6), evidence pipeline (9), preliminary gaps (3), scanner weights,
public pages.

### Environment gotchas
- Windows system env has `MAIL_MAILER=smtp`/`APP_ENV=local` baked in — it
  OVERRIDES .env. Prefix serve with `MAIL_MAILER=log` for local email testing.
- Python 3.12 lives at C:\python312 (not on PATH).
- PesaPal: sandbox keys go in .env (PESAPAL_CONSUMER_KEY/SECRET/IPN_URL);
  checkout degrades gracefully (no crash) when keys are absent.

---

## 11. FILE → FEATURE CHEAT SHEET (when something breaks, look here)

| Symptom / feature | Files to open |
|---|---|
| Scan fails / score wrong | scanner/scanner.py, app/Services/ScannerClient.php, /admin/scanner-checks |
| Scrape returns nothing | scanner/business_scraper.py, app/Services/ScraperClient.php, `php artisan scrape:run --stats` |
| Discovery finds nothing / errors | scanner/osm_discovery.py, app/Services/DiscoveryService.php, discovery_runs.error text — remember: runs MUST go through the queue worker (Windows DNS quirk) |
| Invoice not created | app/Observers/EnquiryObserver.php, app/Services/InvoiceService.php |
| Receipt not generated | app/Services/InvoiceSettlementService.php, `php artisan payments:reconcile --dry-run`, storage/logs/laravel.log |
| Payment stuck pending | app/Console/Commands/ReconcilePaymentsCommand.php, app/Services/PesapalService.php |
| Staff emails missing | app/Services/NotificationService.php, notifications table, MAIL_MAILER |
| Page broken / 500 | routes/web.php first (find the route) → its controller → the blade view |
| Pricing wrong | /admin/packages, config/owers.php (deposit %) |
| Scheduler not running | `php artisan schedule:work` window alive? routes/console.php |

---

## 12. GLOSSARY (terms you'll see in the code)

- **Blade** — Laravel's HTML template engine (the .blade.php files)
- **Eloquent** — Laravel's database layer; each Model class = one table
- **Migration** — version control for the DB schema (30 of them, in order)
- **Mailable** — an email class (subject, view, attachments)
- **Observer** — code that runs automatically when a model changes
- **Queue/Job** — background work (queue:work processes them)
- **Scheduler** — cron-like timers in routes/console.php
- **IPN** — Instant Payment Notification (PesaPal's server-to-server ping)
- **JSON-LD** — structured data embedded in websites (goldmine for the scraper)
- **PesaPal** — Tanzanian payment gateway (mobile money)
- **robots.txt** — a website's "please don't crawl" file (we respect it)
- **Artisan** — Laravel's command-line tool (`php artisan …`)
```
