# Oweru Tech Solutions — Platform

The company's own digital arm: a public marketing site, a free website-scanner
lead magnet, a sales pipeline, PesaPal invoicing, and 24/7 scrapers that hunt
for leads automatically — all in one Laravel application.

## What it does

| Area | What you get |
|---|---|
| **Public site** | Packages, care plans, enquiry form, FAQ |
| **Website scanner** | 23 health checks across 8 areas (0–100 score), instant report, auto-creates a lead |
| **Sales pipeline** | Enquiry stages (`new → qualified → diagnostic_paid → proposal_sent → won/lost`), kanban board, stuck-stage reminders |
| **Invoicing** | Auto-invoice on `proposal_sent` (EnquiryObserver), 50% deposit terms, PesaPal mobile-money checkout, automatic PDF receipts, overdue reminders, payment reconciliation |
| **24/7 scraper** | URL queue processed every 5 min: extracts contacts, services, reviews; auto health-scans new businesses; watchdog diffs re-scrapes and alerts on changes |
| **Discovery** | OpenStreetMap probe finds local businesses with no website → outreach list |

## Stack

- **Laravel 11** (PHP) — app, API, billing, admin panel
- **Python / Flask** — scanner + scraper engine (`scanner/`, port 5000)
- **MySQL** with automatic SQLite fallback (local dev)
- **Tailwind CSS v4** · **DomPDF** · **PesaPal** (Tanzanian mobile money)

The PHP and Python halves are separate processes that talk over HTTP
(`ScannerClient` / `ScraperClient` → `http://localhost:5000`, API-key header).

## Quick start (local)

Requires PHP 8.2+, Composer, Node.js, Python 3.12, MySQL (optional).

```bash
composer install
npm install && npm run build
cp .env.example .env          # then edit DB / PesaPal keys
php artisan key:generate
php artisan migrate --seed
```

Then start the four services (or just double-click **`start.bat`**):

```bash
php artisan serve                        # app        → http://127.0.0.1:8000
C:\python312\python.exe scanner\scanner.py   # engine    → port 5000
queue-worker.bat                         # queue:work (auto-restarting)
php artisan schedule:work                # scrapers, reconcile, reminders
```

Default logins (local seed): `admin@oweru.co.tz / change-me-now`

## Tests

```bash
php artisan test
```

Runs on an isolated in-memory SQLite database — it can never touch your dev
data.

## Documentation

- [`SYSTEM-STUDY-GUIDE.md`](SYSTEM-STUDY-GUIDE.md) — full architecture walkthrough, folder by folder, flow by flow
- [`USER-GUIDE.md`](USER-GUIDE.md) — day-to-day usage
- [`VPS-DEPLOYMENT.md`](VPS-DEPLOYMENT.md) / [`VERCEL-DEPLOYMENT.md`](VERCEL-DEPLOYMENT.md) — deployment

## Security notes

- Secrets live in `.env` (never committed); `.env.example` documents every key
- Local DB files (`*.sqlite`, `*.bak`) are git-ignored — never commit them
- PesaPal sandbox keys go in `.env` (`PESAPAL_CONSUMER_KEY/SECRET/IPN_URL`);
  checkout degrades gracefully when they are absent
