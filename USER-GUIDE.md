# 📘 OWERU TECH SOLUTIONS — USER GUIDE

This guide is for **anyone who has never seen this system before**. No
technical knowledge needed. Read it top to bottom and you will know every
screen, every button, and everything that happens automatically behind the
scenes.

> 🛠️ Want the technical details instead (folders, code, database)? Read
> `system-study-guide.md`. This document is about *using* the system.

---

## PART 1 — WHAT IS THIS SYSTEM?

Oweru Tech Solutions is a company that builds and fixes websites for other
businesses. This system is the company's own website + back office, and it
does four jobs:

| Job | Plain language |
|---|---|
| **Showcase** | A public website advertising packages, prices and care plans |
| **Find customers** | A free "Website Check" tool + automatic business finders that collect leads |
| **Track sales** | A pin-board (pipeline) where every lead is moved from "new" to "won" |
| **Get paid** | Invoices, 50% deposits, PesaPal mobile-money checkout, automatic receipts |

There are **two kinds of users**:

1. **Visitors** — potential customers. They see the public website. No login.
2. **Staff** — you and your team. You log in and see the Admin Panel.

---

## PART 2 — STARTING THE SYSTEM (staff, once per day)

Double-click **`start.bat`** in the project folder. It opens 4 black windows
and your browser. Leave all 4 windows open — each one is a running part:

| Window | What it is | What happens if you close it |
|---|---|---|
| Oweru Laravel (:8000) | The website itself | Nobody can open the site |
| Oweru Scanner (:5000) | The engine that checks and reads websites | Scans and scraping stop working |
| Oweru Queue Worker | The "to-do list" runner (PDFs, emails, batch scans) | Reports/emails stop being made |
| Oweru Scheduler | The alarm clock (auto-scraper, reminders) | Automatic 24/7 jobs stop |

💡 **If a window crashes**, just close it and run `start.bat` again. The
queue worker even restarts itself automatically if it crashes.

**Log in:** click "Login" → `admin@oweru.co.tz` / `change-me-now`
(second account: `manager@oweru.co.tz` / `change-me-too`).
⚠️ Change these passwords before real use.

---

## PART 3 — THE PUBLIC WEBSITE (what your customers see)

Your site has 4 pages, linked in the top menu:

### 🏠 Home (`/`)
The landing page: who you are, featured packages, care plans, and two big
buttons — **"Free Website Check"** and **"Get Started"**.

### 🧰 Services (`/services`)
All sellable packages grouped by service line (software development, web,
CRM, networking) plus monthly **Care Plans**. Each card has an **Enquire**
button that opens the enquiry form with that package pre-selected.

### 🔍 Website Check (`/website-check`) — your lead magnet
Any visitor can type a website address and get a **free instant health
score from 0–100**, plus the top 3 problems found. Behind the scenes the
engine runs 23 checks (security, mobile-friendliness, speed, trust…).

- After the scan, the visitor is offered a **full PDF report** — if they
  request it (name + email), they instantly become a **lead in your
  pipeline**. You'll get a staff email about it.
- You see every scan at **Admin → Scans**.

### ✉️ Get Started (`/enquiry`)
The enquiry form: name, business, email, phone, package interest, problem
description, budget. Submitting creates a lead in the pipeline and emails
staff. Every lead from every source lands here.

### 💸 Paying an invoice (visitor's view)
When you send an invoice, the customer gets an email with a payment link
(e.g. `/pay/invoice-deposit/5`). They choose PesaPal mobile money, pay, and:
- the invoice updates itself (part_paid → paid),
- a **PDF receipt is generated and emailed automatically** when fully paid,
- the payment status page shows live progress at `/pay/status/{ref}`.

---

## PART 4 — THE ADMIN PANEL (your daily workspace)

Log in at `/login`. Everything lives behind the black left sidebar:

```
MAIN          Dashboard
BUILD 1       Service Packages · Care Plans
BUILD 2       Enquiries · Pipeline
BUILD 3       Websites · Scans · Recommendations · Scanner Checks
              Website Discovery · No-Website Leads · Scraper · Auto-Scraper Queue
REPORTS       Reports · Payments · Invoices
```

### 4.1 Dashboard (`/admin`)
Your morning overview: recent leads, recent scans, low-score prospects
(businesses worth calling), engine status, and money at a glance.

### 4.2 Service Packages (`/admin/packages`)
Create and edit what you sell: name, price (TZS/USD), features, which
service line it belongs to. Changes appear on the public Services page
immediately.

### 4.3 Care Plans (`/admin/care-plans`)
Monthly subscription plans (hosting, updates, support). Same editing idea.

### 4.4 Enquiries (`/admin/enquiries`) — the lead list
Every lead from every source: the form, the scanner, the scraper, discovery.
Open a lead and you can:
- **Change stage** (dropdown — see the pipeline below)
- **Assign an owner** (which staff member handles it)
- **Write notes** (call outcomes, requirements)

⭐ **The magic moment:** the instant you set a lead's stage to
**proposal_sent**, the system **automatically creates the invoice and
emails it to the customer** (50% deposit terms). No button to press.

### 4.5 Pipeline (`/admin/pipeline`)
The same leads as a kanban board — one column per stage. Drag your eye
across it to see the health of the business:

```
new → qualified → diagnostic_paid → proposal_sent → won / lost
```

| Stage | Meaning |
|---|---|
| new | Fresh lead, nobody has looked at it yet |
| qualified | A human confirmed it's a real opportunity |
| diagnostic_paid | Customer paid TZS 10,000 for the full report (PesaPal moves the stage for you) |
| proposal_sent | Proposal + invoice sent (invoice auto-created here) |
| won / lost | Deal closed |

A daily 09:00 email tells staff about any lead stuck for 3+ working days,
so nothing rots quietly.

### 4.6 Websites (`/admin/websites`)
Every website you track. From here you can **run a scan** on any site, and
**"Scan All"** for a batch. Important: sites marked **excluded** can never
be scanned again (use this when a business asks to opt out).

### 4.7 Scans (`/admin/scans`)
The history of every scan ever made — public ones, admin ones, and
auto-scans of scraped businesses. Open one to see all 23 check results,
each with:
- **evidence** — what was actually found on the site
- **finding** — what's wrong
- **consequence** — why it matters to the business owner

Scores: **Strong 80+ · Adequate 60–79 · Weak 40–59 · Critical <40**.

### 4.8 Recommendations (`/admin/recommendations`)
The sales-brain: a table mapping each failed check to a recommended Oweru
service ("No SSL → recommend security package"). Edit the mapping anytime;
it feeds scan reports and the AI pitch emails.

### 4.9 Scanner Checks (`/admin/scanner-checks`)
The 23 checks themselves. **Enable/disable** a check or change its
**weight** (points). Your weights are sent to the scanner engine on every
scan — no restart needed.

### 4.10 Website Discovery (`/admin/discovery`)
Find businesses without typing a single URL. Pick a **city** (type-ahead:
Dar es Salaam, Arusha, Mwanza…) and a **category** (hotels, restaurants,
schools…), press run. The system searches OpenStreetMap and:
- businesses **with** a website → queued into the Auto-Scraper
- businesses **without** a website → the No-Website Leads list
  ("they need a website — we build those!")

One run = one query against shared public infrastructure, so don't loop it.

### 4.11 No-Website Leads (`/admin/discovery/leads`)
The outreach goldmine from discovery: businesses with **no website at
all**. Each row has map links and a **mark-contacted** button.

### 4.12 Scraper (`/admin/scraped-businesses`)
Every business the scraper has extracted: name, email, phone, address,
services, reviews, ratings. On each business page you can:
- **Re-scrape** — fetch fresh data (the watchdog compares it to the last
  snapshot and alerts on changes)
- **Run Health Scan** — full 0–100 scan of that business
- **Add to Leads** — push it into the pipeline as a new enquiry

Also here: **Export** to CSV, and paste-a-URL manual scraping (V1 style).

### 4.13 Auto-Scraper Queue (`/admin/scrape-targets`)
The 24/7 lead machine. Paste a batch of URLs (up to your heart's content),
and the scheduler works through them forever:

- processes up to **10 targets every 5 minutes** (oldest first)
- re-scrapes each target every **7 days** to keep data fresh
- a failed target is retried after **6 hours**
- new businesses are **auto-health-scanned** (up to 5 per pass)
- directory pages auto-queue the businesses they link to

Per-target buttons: **Pause · Resume · Run Now · Delete**, plus
**Check All**. The watchdog watches every re-scrape and emails staff when a
business's rating drops, a bad review appears, contact details change, or
the site goes down/back up (max one email per business per 24h).

### 4.14 Reports (`/admin/reports`)
All generated PDF scan reports. Download any of them, or generate a fresh
one for a scan.

### 4.15 Payments (`/admin/payments`)
Every payment attempt (pending → completed / failed), with PesaPal
references. This is your truth-list when a customer says "I paid!".

### 4.16 Invoices (`/admin/invoices`)
Every invoice: number (`OWU-INV-2026-0001`), customer, total, deposit,
status, and buttons to **issue, download PDF, view receipt, or cancel**.

| Status | Meaning |
|---|---|
| draft | Created but not sent yet |
| issued | Sent to customer, deposit due in 7 days |
| part_paid | Deposit received, balance outstanding |
| paid | Fully paid — receipt already auto-emailed |
| cancelled | Voided (excluded from all reminders) |

---

## PART 5 — HOW THE MONEY FLOWS (a deal, start to finish)

1. Lead reaches **proposal_sent** → invoice auto-created and emailed
   (PDF attached; 50% deposit, due in 7 days).
2. Customer clicks the payment link → pays via **PesaPal** mobile money.
3. The system confirms the payment **twice over** (browser callback +
   server IPN) and locks against double-counting, so the receipt fires
   **exactly once** no matter what.
4. Deposit paid → invoice shows **part_paid**. Fully paid → **paid** and a
   PDF receipt is emailed automatically.
5. Safety nets, all automatic:
   - every **10 min** a reconcile job asks PesaPal about any payment whose
     confirmation got lost, and settles it (receipt fires then);
   - overdue deposits email the customer every **3 days** (max 4 emails)
     **and** alert staff with the customer's phone number so you can call.

Deposit % and due days are configurable (`OWERU_DEPOSIT_PERCENT`,
`OWERU_DEPOSIT_DUE_DAYS`) — default 50% / 7 days.

---

## PART 6 — WHAT RUNS AUTOMATICALLY (the scheduler)

| When | Job | What it does |
|---|---|---|
| Every 5 min | Auto-scraper | Scrapes the next batch of due targets |
| Every 10 min | Payment reconcile | Settles completed payments whose confirmation was missed |
| Daily 09:00 | Stage reminders | Emails staff about leads stuck 3+ working days |
| Daily 10:00 | Overdue reminders | Chases unpaid deposits (customer + staff) |

Everything above needs the **Scheduler window** from `start.bat` to be open.

Every new scraped business is also **auto-health-scanned** and its
weaknesses mapped to your services — zero clicks. (Both auto-scan and the
watchdog can be switched off with `OWERU_AUTO_SCAN=false` /
`OWERU_SCRAPER_WATCHDOG=false` in `.env`.)

---

## PART 7 — EMAILS THE SYSTEM SENDS

| Email | To | When |
|---|---|---|
| Scan results | Visitor | After their free Website Check |
| Full report + pitch | Staff/visitor | Report requested; findings mapped to recommendations |
| New-lead alert | Staff | Someone submits the enquiry form or requests a report |
| Invoice | Customer | Lead hits proposal_sent (PDF attached) |
| Receipt | Customer | Invoice fully paid (PDF attached) |
| Deposit reminder | Customer | Deposit overdue (every 3 days, max 4) |
| Deposit alert | Staff | Deposit overdue — includes customer phone number |
| Stage reminder | Staff | Lead stuck 3+ working days |
| Watchdog alert | Staff | Rating drop / bad review / contact change / site down |

💡 **Testing emails locally:** run the app with
`MAIL_MAILER=log php artisan serve` — all emails are written to
`storage/logs/laravel.log` instead of being sent.

---

## PART 8 — WHEN SOMETHING SEEMS WRONG (troubleshooting)

| Symptom | What to do |
|---|---|
| Site won't open | Is the **Laravel (:8000)** window alive? Re-run `start.bat` |
| Scan fails or hangs | Is the **Scanner (:5000)** window alive? The admin dashboard also shows engine status |
| No emails arriving | On Windows, system env can force SMTP — start the app with `MAIL_MAILER=log` and read `storage/logs/laravel.log` |
| Customer paid but no receipt | Don't panic: the reconcile job fixes it within 10 minutes. Check Admin → Payments for the attempt |
| Invoice never got created | The lead must reach **proposal_sent** — check its stage in Enquiries |
| Auto-scraper not moving | Is the **Scheduler** window alive? Then check targets aren't paused in the queue |
| Discovery run does nothing | Runs go through the queue worker — make sure that window is alive, then check `discovery_runs` for an error message |
| Scores seem wrong | Adjust check weights at Admin → Scanner Checks (takes effect on the next scan) |
| Page shows an error | Note the URL and tell your developer — routes map 1:1 to pages |

---

## PART 9 — QUICK REFERENCE CARD

**URLs**

| Where | Address |
|---|---|
| Public site | http://127.0.0.1:8000 |
| Website Check | http://127.0.0.1:8000/website-check |
| Enquiry form | http://127.0.0.1:8000/enquiry |
| Admin login | http://127.0.0.1:8000/login |
| Admin dashboard | http://127.0.0.1:8000/admin |

**Daily staff routine**

1. Open `start.bat`, wait for 4 windows + browser.
2. Log in → **Dashboard**: new leads? engine green?
3. **Pipeline**: move stages, assign owners, write notes after calls.
4. **Discovery / Auto-Scraper Queue**: top up the lead machine.
5. **Invoices / Payments**: check nothing is stuck; call overdue customers.

**Glossary (5-second version)**

- **Lead / Enquiry** — a potential customer in your pipeline
- **Stage** — where the lead is between "new" and "won"
- **Scan** — the 23-point website health check (score 0–100)
- **Scrape** — extracting a business's contact info from its website
- **Discovery** — finding businesses on OpenStreetMap automatically
- **Watchdog** — the change-detector that diffs re-scrapes
- **Invoice / Deposit / Receipt** — the bill / the first 50% / the proof of payment
- **PesaPal** — the mobile-money payment gateway
- **IPN** — PesaPal's server-to-server "payment happened" ping
- **Scheduler / Queue worker** — the alarm clock / the to-do-list runner
