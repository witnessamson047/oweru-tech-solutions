# 🖥️ VPS DEPLOYMENT GUIDE — Oweru Tech Solutions (the cheap way, ~$5/mo)

One small VPS runs the ENTIRE system as designed — marketing site, admin,
payments, queue, scheduler AND the 24/7 scanner engine. Five containers, one
command to deploy.

```
                      INTERNET (port 80/443)
                             │
 ┌───────────────────────────┼──────────────────────────────┐
 │  VPS (Ubuntu 24.04)       │                              │
 │  ┌─────────┐  ┌─────────┐ │ ┌────────┐  ┌───────┐      │
 │  │  app    │  │  queue  │ │ │ engine │  │sched. │      │
 │  │ Apache  │  │ queue:  │ │ │ Flask  │  │ sched │      │
 │  │ :80     │  │ work    │ │ │ :5000  │  │ :work │      │
 │  └────┬────┘  └────┬────┘ │ └───┬────┘  └───┬───┘      │
 │       └──────┬─────┴────────────┴───────────┘          │
 │         ┌────┴─────┐                                    │
 │         │  MySQL   │  (volume: db-data, survives        │
 │         │  db:3306 │   reboots + redeploys)             │
 │         └──────────┘                                    │
 └─────────────────────────────────────────────────────────┘
```

## 1. Create the VPS (~$5/mo)

Any Ubuntu 24.04 box with 1–2 GB RAM works:

- **Hetzner Cloud** — CX22 (2 vCPU/4GB) ≈ €3.8/mo, region Falkenstein (EU, low ping to TZ)
- **DigitalOcean** — Basic Droplet 1GB ≈ $6/mo
- Choose **Ubuntu 24.04**, add your SSH key (or password), note the IP.

## 2. One-time server setup (paste into the VPS terminal)

```bash
ssh root@YOUR-VPS-IP

# Docker + compose plugin
curl -fsSL https://get.docker.com | sh

# Git + swap (protects MySQL on a 1GB box)
apt-get update && apt-get install -y git
fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile
echo '/swapfile none swap sw 0 0' >> /etc/fstab
```

## 3. Deploy the app

```bash
cd /opt
git clone https://github.com/witnessamson047/oweru-tech-solutions.git oweru
cd oweru

cp .env.vps.example .env
nano .env            # fill: APP_KEY, DB passwords, SCANNER_API_KEY, mail, PesaPal
```

Generate the APP_KEY **on your own PC** first: `php artisan key:generate --show`

Pick a long random `SCANNER_API_KEY`, e.g. from the VPS:
`openssl rand -hex 32`

Then start everything:

```bash
docker compose up -d --build
docker compose logs -f app     # watch the first migration finish, Ctrl-C to exit
```

Open `http://YOUR-VPS-IP` — the site is live. Admin panel at `/admin`.

## 4. HTTPS + a real domain (recommended before telling customers)

Point a domain (e.g. `oweru.co.tz`) A-record at the VPS IP, then:

```bash
apt-get install -y certbot && docker compose stop app
certbot certonly --standalone -d oweru.co.tz   # ports 80/443 must be free
docker compose up -d
```

Then add a small nginx/caddy TLS container in front (or ask me — I'll wire
Caddy, which auto-renews). Update in `.env`:
`APP_URL=https://oweru.co.tz`, `PESAPAL_IPN_URL=https://oweru.co.tz/payment/ipn`,
then `docker compose up -d` again.

## 5. Verify (2 minutes)

| Check | Command / action |
|---|---|
| All 5 containers up | `docker compose ps` → all "running"/"healthy" |
| Site loads | Browser → `http://YOUR-VPS-IP` |
| Engine alive | `curl localhost:5000/health` from the VPS → `{"status":"ok"...}` |
| Public scan works | `/website-check` → scan any site → score appears |
| Queue works | Admin → trigger a batch scan → results fill in |
| Scheduler alive | `docker compose logs scheduler --tail 20` |
| Admin login | `/admin` with the seeded credentials |

## 6. Day-to-day operations

```bash
docker compose ps                 # status of all services
docker compose logs -f app        # app logs (stderr channel)
docker compose pull && docker compose up -d --build   # deploy an update (git pull first)
docker compose restart queue      # restart just one service
docker compose exec db mysqldump -u root -p oweru_tech_solutions > backup.sql   # DB backup
```

Local dev on your PC still works exactly as before (start.bat) — the VPS is
the always-on copy. `git push`, then on the VPS `git pull && docker compose
up -d --build` to ship changes.

## 7. What this fixes vs. Vercel

| Ability | Vercel (serverless) | This VPS |
|---|---|---|
| Queue worker (PDFs, emails, batch scans) | sync/inline | real `queue:work` |
| Scheduler (24/7 scraper, reminders) | ❌ none | `schedule:work` container |
| Writable storage (PDF receipts on disk) | /tmp, ephemeral | persistent volume |
| Scanner engine | ❌ separate service needed | included |
| Sessions | cookie | database driver |
| ToS | ❌ needs $20 Pro for business | your own box, no restriction |
