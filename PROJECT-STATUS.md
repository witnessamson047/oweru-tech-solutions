# 📋 PROJECT STATUS — read me first (for Buffy / any developer)

**Project:** Oweru Tech Solutions — website health scanner + lead pipeline
**Owner:** Witness (GitHub: witnessamson047)
**Last updated:** 2026-09-11

## What this project is
Laravel 11 + Python Flask website scanner. Builds 1–3 of the brief are COMPLETE and verified end-to-end:
1. Service packages page (TZS/USD, admin-editable)
2. Enquiry + qualification pipeline (New → Qualified → diagnostic_paid → proposal_sent → won/lost)
3. Site Health Scanner (23 checks, 8 areas, 100 pts) + one-page PDF reports + recommendations engine

Plus: queued batch scanning ("⚡ Scan All"), enquiry email notifications, 3-day stage reminders.

## GitHub
Repo: https://github.com/witnessamson047/oweru-tech-solutions (private, branch `main`)
Push workflow: `git add .` → `git commit -m "message"` → `git push`

## How to run everything (3 terminals)
```bash
php artisan serve                                # app → http://127.0.0.1:8000
C:\python312\python.exe scanner\scanner.py       # scanner engine → port 5000 (REQUIRED for scans)
php artisan queue:work --tries=1 --timeout=300   # background batch scans
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

## Known issues / next steps (in priority order)
1. ~~MISSING VIEWS~~ FIXED 2026-09-11: admin/websites/create.blade.php and edit.blade.php built (were 500ing). Feature tests added (tests/Feature/AdminWebsitesTest.php, 5 tests).
2. Doc gaps from the audit: public scanner shows 5 findings (doc says 3); scanner weights hardcoded in scanner.py (should read admin-configurable scanner_checks table); no re-scan comparison display (improved/declined).
3. User #1 admin@oweru.com was deleted from DB intentionally.
4. Test data exists: Demo Cafe (website 1), example.com scans (scores 64/Adequate), Jane/Bob/Carol/Dave/Grace test enquiries. Clean up before production.

## What was verified working today
- Real scan of https://example.com: completed, 64/100 Adequate, 23 results stored
- Batch scan: 4 jobs queued, returned in 2s, all scanned in background
- Notifications: new-enquiry alert status=sent; stage reminder status=sent
- PDF report generate + download via authenticated HTTP session
- Tests: 7 passed (php artisan test)
