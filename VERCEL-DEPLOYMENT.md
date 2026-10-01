# 🚀 VERCEL DEPLOYMENT GUIDE — Oweru Tech Solutions

How the live deployment works and exactly what to click/type to go live.
Written for Witness (non-technical friendly) — follow it top to bottom.

---

## 1. What runs where on Vercel

```
 Visitor ──▶ Vercel Edge (https, CDN)
                │
                ├─ /build/*  ──▶ static files (public/build, committed CSS/JS)
                ├─ /images/* ──▶ static files (public/images)
                │
                └─ everything else ──▶ ONE serverless PHP function
                                        api/index.php  (vercel-php@0.7.4,
                                        PHP 8.3) → boots Laravel → your app
```

- **Whole Laravel app** (public site, scanner page, admin, payments) = the
  single `api/index.php` function. `vercel.json` routes everything to it.
- **Python scanner/scraper engine does NOT run on Vercel** (no Python worker,
  no long processes). Set `SCANNER_SERVICE_URL` to wherever the engine is
  hosted; until then the scanner page degrades gracefully.
- **No queue worker / scheduler on Vercel.** Jobs run inline (sync). The 24/7
  scrape/discovery automation keeps running from your local start.bat setup,
  or move it to a small VPS later.

## 2. What changed in the repo to make this possible

| File | Change |
|---|---|
| `vercel.json` | builds (PHP function + static assets) + routes (assets, then catch-all) |
| `api/index.php` | entrypoint: forces /tmp-safe paths (views, logs, sessions, cache) then boots Laravel |
| `.vercelignore` | keeps `.env`, tests, dev logs, scanner/ out of the upload |
| `app/Support/LocalPath.php` | PDF reports/receipts go to `storage/app` normally, or `/tmp` (or `OWERU_STORAGE_DIR`) on Vercel |
| `bootstrap/app.php` | `trustProxies(at: '*')` so HTTPS/URLs are correct behind Vercel |
| `.gitignore` | `public/build` is now COMMITTED (Vercel can't run npm); `public/hot` never committed |
| `composer.json` | `vercel` build script (runs migrations + vite build during deploy) |

## 3. Go live — step by step

1. **Create the Vercel project**
   - Sign up / log in at vercel.com with your GitHub account.
   - Add New → Project → Import `witnessamson047/oweru-tech-solutions`.
   - Framework Preset: **Other**. Leave build command + output dir empty
     (vercel.json drives everything). Deploy — it will succeed but the app
     will error on the database until step 3. That's expected.

2. **Add the environment variables** (Project → Settings → Environment
   Variables — paste the checklist below).

3. **Get a database.** Vercel has no database. Use one of:
   - **Aiven / PlanetScale / Railway / free MySQL host** — any reachable
     MySQL. Then set `DB_CONNECTION=mysql` + host/port/db/user/password.
   - **TiDB Cloud** free tier also works (MySQL protocol).
   The app already falls back to SQLite if MySQL is unreachable, but on
   Vercel the SQLite file is read-only → the database is the one thing you
   really must provide.

4. **Generate an app key**: locally run
   `php artisan key:generate --show` and paste the `base64:...` value as
   `APP_KEY` (Production).

5. **Redeploy** (Deployments → ⋯ → Redeploy). If migrations did not run in
   the build, they run there automatically — or run them from your machine
   against the cloud DB once.

## 4. Environment variable checklist (paste into Vercel)

```
APP_NAME="Oweru Tech Solutions"
APP_ENV=production
APP_KEY=base64:PASTE_GENERATED_KEY
APP_DEBUG=false
APP_URL=https://YOUR-PROJECT.vercel.app

# — Database (REQUIRED) —
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_PORT=3306
DB_DATABASE=oweru_tech_solutions
DB_USERNAME=...
DB_PASSWORD=...
DB_FALLBACK_TO_SQLITE=false

# — Sessions/cache: MUST be cookie/array on Vercel (no writable FS) —
SESSION_DRIVER=cookie
CACHE_STORE=array
QUEUE_CONNECTION=sync

# — Mail (use a real SMTP or a provider like Brevo/Mailgun) —
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@oweru.co.tz

# — Scanner engine (leave empty until the engine is hosted somewhere) —
SCANNER_SERVICE_URL=
SCANNER_API_KEY=

# — PesaPal (live keys when going live; sandbox keys for testing) —
PESAPAL_CONSUMER_KEY=
PESAPAL_CONSUMER_SECRET=
PESAPAL_BASE_URL=https://pay.pesapal.com/pesapalv3
PESAPAL_IPN_URL=https://YOUR-PROJECT.vercel.app/payment/ipn

# — PDF/report storage: /tmp on Vercel (fine; e-mail is the durable copy) —
OWERU_STORAGE_DIR=/tmp
```

PesaPal callbacks after go-live (they must be reachable publicly — they will
be, on Vercel): `/payment/callback` and `/payment/ipn`.

## 5. Limits & honest notes

- **Free (Hobby) plan**: 12s function timeout, 100 GB bandwidth — fine for
  the marketing site + admin + normal traffic.
- **Filesystem is read-only** (except `/tmp`, wiped per invocation). PDFs are
  still delivered — attached to the e-mail at generation time.
- **Sessions are cookie-based** on Vercel — admin login works, but always
  use HTTPS (Vercel gives you this by default).
- **The scanner check lives elsewhere**: run start.bat locally, or host the
  Flask engine on a VPS/Railway and point `SCANNER_SERVICE_URL` at it.
- To deploy: `git push` (auto-deploys) or `vercel` from the project folder.
