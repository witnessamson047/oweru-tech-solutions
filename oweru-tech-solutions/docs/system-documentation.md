# Oweru International Ltd — Tech Solutions Platform
## System Development & Technical Implementation Documentation

**Version:** 1.0  
**Date:** September 2026  
**Status:** Builds 1–3 Implemented · Build 4 Pending

---

## 1. Executive Summary

This platform supports Oweru International Ltd in:
- Presenting technical service packages, prices and delivery times
- Capturing and qualifying enquiries in a structured pipeline
- Scanning public business websites for measurable technical issues
- Producing one-page PDF reports for lead generation and sales
- Converting scanner users into qualified enquiries

The system is built on **Laravel 11 + MySQL + Python (Flask scanner service)**. Laravel is the main business application; Python is the specialist scanning engine. The two communicate via REST/HTTP.

Builds 1–3 are fully implemented. Build 4 (Signal Monitoring) is pending.

---

## 2. Technology Stack

| Layer | Technology | Role |
|-------|-----------|------|
| Backend | Laravel 11 (PHP 8.2+) | Auth, admin, business logic, reports, APIs |
| Frontend | Blade + Tailwind CSS 4 + vanilla JS | Public pages, scanner, admin dashboard |
| Database | MySQL | Packages, enquiries, websites, scans, findings |
| Scanner | Python 3 + Flask + requests | Web scraping, technical checks, scoring |
| PDF | barryvdh/laravel-dompdf | One-page scan reports |
| API | REST/HTTP (Laravel ↔ Python) | Scan requests, results, callbacks |

---

## 3. Build Summary

| Build | Module | Status |
|-------|--------|--------|
| Build 1 | Tech Solutions Offer Page | ✅ Implemented |
| Build 2 | Enquiry & Qualification Pipeline | ✅ Implemented |
| Build 3 | Site Health Scanner | ✅ Implemented (Laravel) · 🟡 Python service needs deployment |
| Build 4 | Signal Monitoring | ⏳ Pending |

---

## 4. Database Schema

### 4.1 Users
`users` — Admin/staff accounts
- id, name, email, password, role, email_verified_at, remember_token, timestamps

### 4.2 Service Packages
`service_packages` — Offer packages and prices
- id, name, slug (unique), group (individuals/sme/corporate), description, price_tzs, price_usd, delivery_days, is_featured, active, sort_order, timestamps

### 4.3 Care Plans
`care_plans` — Monthly care plans
- id, name, slug, description, price_tzs, price_usd, is_featured, active, timestamps

### 4.4 Enquiries
`enquiries` — All client enquiries
- id, name, business_name, email, phone, country, package_id (nullable FK), package_name, problem_description, current_cost, budget_range, required_date, stage (new/qualified/diagnostic_paid/proposal_sent/won/lost), source (website/scanner/referral/manual), owner_id (nullable FK), notes, last_stage_changed_at, consent_given, timestamps

### 4.5 Websites
`websites` — Business sites being scanned
- id, business_name, url (unique), sector, status, exclusion_status (active/excluded), exclusion_reason, enquiry_id (nullable FK), timestamps

### 4.6 Scanner Checks
`scanner_checks` — Configurable checks/weights
- id, name, slug (unique), area, weight (points), enabled, description, wording_pass, wording_fail, timestamps

### 4.7 Scans
`scans` — One record per scan
- id, website_id (nullable FK), url, started_at, completed_at, status (pending/running/completed/failed), score, band, source (public/manual/batch), error_message, timestamps

### 4.8 Scan Results
`scan_results` — Individual check outcomes
- id, scan_id (FK), check_id (nullable FK), check_name, area, passed, points, evidence, finding_text, consequence, timestamps

### 4.9 Recommendations
`recommendations` — Suggested technical solutions
- id, check_name, area, finding_example, consequence, solution, service_type, priority (low/medium/high), active, timestamps

### 4.10 Reports
`reports` — Generated report references
- id, scan_id (FK), file_path, generated_at, downloads, timestamps

### 4.11 Package Exclusions
`package_exclusions` — Shared exclusions
- id, description, details, sort_order, active, timestamps

### 4.12 Delivery Commitments
`delivery_commitments` — Delivery commitments
- id, title, description, sort_order, active, timestamps

---

## 5. Scanner Check Model (100-Point System)

| Area | Check | Weight |
|------|-------|--------|
| **Security** (20) | SSL Certificate Valid | 10 |
| | SSL Certificate >30 Days to Expiry | 5 |
| | No Insecure Items on Secure Page | 5 |
| **Mobile** (20) | Responsive Layout | 8 |
| | Readable Body Text (≥14px) | 6 |
| | Tap-Friendly Buttons/Links (≥44px) | 6 |
| **Speed** (15) | Page Loads Within 3s on Mobile | 5 |
| | Total Page <2 MB | 5 |
| | Images Compressed/Sized | 5 |
| **Function** (15) | No Broken Links | 5 |
| | Contact/Enquiry Form Exists | 5 |
| | Phone/Email Are Tappable | 5 |
| **Findability** (12) | Unique Page Title | 3 |
| | Meta Description Present | 3 |
| | Appears for Business Name Search | 3 |
| | Google Business Profile Points to Site | 3 |
| **Trust** (10) | Registered Company Name | 3 |
| | Physical Address Listed | 3 |
| | Privacy Policy Page | 2 |
| | Terms of Service Page | 2 |
| **Commerce** (5) | Online Payment/Booking Path | 5 |
| **Freshness** (3) | Content Changed Within 12 Months | 2 |
| | Current Copyright Year | 1 |

**Score Bands:**
- Under 40 — Critical
- 40–59 — Weak
- 60–79 — Adequate
- 80–100 — Strong

**Scanning rules (mandatory):**
- Respect robots.txt; stop when disallowed
- Maximum 1 request per 2 seconds per host
- Honest User-Agent: `OweruScanner/1.0 (+https://oweru.co.tz; scanner@oweru.co.tz)`
- Scan home page + up to 10 internal pages from main navigation
- Delete raw page content after scoring; retain structured results only
- Never access private/member-only areas

---

## 6. Public Self-Check Flow

1. Visitor enters website URL on `/website-check`
2. JavaScript calls `POST /api/scanner/scan`
3. Laravel validates URL, checks exclusion list, creates scan record
4. Laravel calls Python scanner at `http://localhost:5000/api/scanner/scan`
5. Python performs checks, returns JSON with score/band/results
6. Laravel stores results, returns score + 3 worst findings to frontend
7. Visitor sees score + 3 findings immediately
8. Visitor clicks "Request Full Report" → modal form
9. Form submission: name, business_name, email, phone, consent checkbox
10. `POST /scanner/report-request` creates enquiry (source=scanner)
11. Visitor redirected with success message

---

## 7. Pipeline Stages

```
New → Qualified → Diagnostic Paid → Proposal Sent → Won → Lost
```

Each enquiry tracks: stage, owner, notes, last_stage_changed_at.

**Notifications:** New enquiries trigger alerts. Enquiries stuck >3 working days in same stage trigger reminders.

---

## 8. Admin Dashboard

**URL:** `/admin` (auth required)

**Sidebar navigation:**
- Dashboard
- Build 1: Service Packages, Care Plans
- Build 2: Enquiries, Pipeline
- Build 3: Websites, Scans, Recommendations
- Reports

**Dashboard stats:** Total enquiries, new enquiries, total scans, websites tracked. Recent enquiries table. Recent scans list. Pipeline overview cards.

---

## 9. API Endpoints

### Public
| Method | Path | Purpose |
|--------|------|---------|
| GET | `/` | Home page |
| GET | `/services` | Packages listing |
| GET | `/services/{slug}` | Package detail → redirect to enquiry |
| GET | `/enquiry` | Enquiry form |
| POST | `/enquiry` | Submit enquiry |
| GET | `/website-check` | Scanner page |
| POST | `/scanner/report-request` | Request full report |

### Admin
| Method | Path | Purpose |
|--------|------|---------|
| GET | `/admin` | Dashboard |
| Resource | `/admin/packages` | Package CRUD |
| Resource | `/admin/care-plans` | Care plan CRUD |
| GET/PATCH | `/admin/enquiries/{id}/stage` | Update stage |
| GET/PATCH | `/admin/enquiries/{id}/owner` | Assign owner |
| POST | `/admin/enquiries/{id}/notes` | Update notes |
| GET | `/admin/pipeline` | Kanban pipeline view |
| Resource | `/admin/websites` | Website CRUD |
| PATCH | `/admin/websites/{id}/exclude` | Exclude from scanning |
| GET | `/admin/scans` | Scans listing with filters |
| GET | `/admin/scans/{id}` | Scan detail + results |
| POST | `/admin/scans/{website}/run` | Trigger scan |
| Resource | `/admin/recommendations` | Recommendation mappings |
| GET | `/admin/reports` | Generated reports list |
| GET | `/admin/reports/{scan}/generate` | Generate PDF |
| GET | `/admin/reports/{id}/download` | Download PDF |

### API (Scanner)
| Method | Path | Purpose |
|--------|------|---------|
| POST | `/api/scanner/scan` | Request a scan |
| GET | `/api/scanner/scans/{id}` | Get scan status/results |
| POST | `/api/scanner/callback` | Receive async scan results |

---

## 10. Service Portfolio

### Core Services
1. **Software Development** — Custom systems, workflows, data management, automation
2. **Mobile & Web Design/Development** — Responsive websites, mobile apps, improvements, integrations
3. **CRM Solutions** — Design, development, implementation, customization, maintenance
4. **Network Design & Maintenance** — Planning, design, configuration, maintenance, troubleshooting, support

### Service Packages
| Group | Package | TZS | USD | Days |
|-------|---------|-----|-----|------|
| Individuals | Starter Website | 500,000 | $200 | 7 |
| Individuals | Professional Portfolio | 1,200,000 | $480 | 14 |
| SMEs | Business Website | 3,000,000 | $1,200 | 21 |
| SMEs | E-Commerce Starter | 5,000,000 | $2,000 | 30 |
| Corporate | Corporate Platform | 10,000,000 | $4,000 | 45 |
| Corporate | Digital Transformation Suite | 20,000,000 | $8,000 | 60 |

### Care Plans
| Plan | TZS/mo | USD/mo |
|------|--------|--------|
| Basic Care | 150,000 | $60 |
| Standard Care | 400,000 | $160 |
| Premium Care | 800,000 | $320 |

### Delivery Commitments
- Clear Timelines — Fixed delivery dates before work begins
- Regular Updates — Weekly progress reports
- Quality Assurance — Every deliverable tested before handover
- Post-Launch Support — 30 days free bug fixes

### Shared Exclusions
- Custom ERP/accounting system integration
- Government regulatory compliance audits
- Physical branding or print design
- Social media account management
- Third-party API integration fees
- Domain name registration/transfer
- Content writing beyond initial setup
- Stock photography/videography

---

## 11. File Structure

```
oweru-tech-solutions/
├── docs/
│   └── system-documentation.md    # This file
├── scanner-specs/
│   └── scanner-overview.md        # Python scanner spec
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── HomeController.php
│   │   │   ├── PackageController.php
│   │   │   ├── EnquiryController.php
│   │   │   ├── ScannerController.php
│   │   │   └── Api/
│   │   │       └── ScannerApiController.php
│   │   └── Admin/
│   │       ├── DashboardController.php
│   │       ├── PackageController.php
│   │       ├── EnquiryController.php
│   │       ├── PipelineController.php
│   │       ├── WebsiteController.php
│   │       ├── ScanController.php
│   │       ├── CarePlanController.php
│   │       ├── RecommendationController.php
│   │       └── ReportController.php
│   └── Models/
│       ├── User.php
│       ├── ServicePackage.php
│       ├── CarePlan.php
│       ├── Enquiry.php
│       ├── Website.php
│       ├── Scan.php
│       ├── ScanResult.php
│       ├── ScannerCheck.php
│       ├── Recommendation.php
│       ├── Report.php
│       ├── PackageExclusion.php
│       └── DeliveryCommitment.php
├── database/
│   ├── migrations/  (12 migrations)
│   └── seeders/     (7 seeders)
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   │   ├── public.blade.php
│   │   │   └── admin.blade.php
│   │   ├── pages/
│   │   │   ├── home.blade.php
│   │   │   ├── enquiry.blade.php
│   │   │   └── packages.blade.php
│   │   ├── scanner/
│   │   │   └── index.blade.php
│   │   ├── reports/
│   │   │   └── scan-report.blade.php
│   │   └── admin/
│   │       ├── dashboard/index.blade.php
│   │       ├── packages/index.blade.php
│   │       ├── care-plans/index.blade.php
│   │       ├── enquiries/index.blade.php
│   │       ├── enquiries/show.blade.php
│   │       ├── pipeline/index.blade.php
│   │       ├── websites/index.blade.php
│   │       ├── websites/show.blade.php
│   │       ├── scans/index.blade.php
│   │       ├── scans/show.blade.php
│   │       ├── recommendations/index.blade.php
│   │       └── reports/index.blade.php
│   ├── js/app.js
│   └── css/app.css
├── routes/
│   ├── web.php
│   └── api.php
├── scanner/
│   └── scanner.py              # Python Flask scanner service
├── public/
│   └── build/                  # Vite-compiled assets
├── composer.json
├── package.json
└── vite.config.js
```

---

## 12. Known Gaps & Next Steps

### Implemented
- ✅ Build 1: Offer page, packages, care plans, exclusions, commitments, currency toggle
- ✅ Build 2: Enquiry form, pipeline, stage management, owner assignment, notes
- ✅ Build 3: Scanner UI, scan triggering, results display, report generation (PDF), recommendation engine

### Needs Attention
1. **Python scanner service** — `scanner/scanner.py` created but not yet deployed/running. Needs `requests`, `python-dateutil` installed. Start with `python scanner.py`.
2. **ScannerCheck admin CRUD** — Scanner checks are seeded but there's no admin UI to edit weights/wording. The `ScannerCheck` model and migration exist but no controller/views.
3. **Notifications** — The `notifications` table is in the spec but not yet implemented. Email/WhatsApp alerts for new enquiries and 3-day reminders are missing.
4. **Batch scan endpoint** — Internal staff need to scan 50 sites at once for testing.
5. **WhatsApp integration** — Alert channel needs WhatsApp API configuration.
6. **Build 4 (Signal Monitoring)** — Not started.

---

## 13. Configuration

Environment variables (`.env`):
```
SCANNER_SERVICE_URL=http://localhost:5000
SCANNER_API_KEY=your-api-key
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=oweru_tech_solutions
DB_USERNAME=root
DB_PASSWORD=
```

Scanner checks are configurable from the `scanner_checks` table. Weights and wording can be adjusted after real scan data is collected.

---

## 14. Acceptance Criteria

### Build 1 ✅
- [x] Admin can edit packages, prices, delivery times
- [x] Enquiry buttons preserve selected package
- [x] TZS and USD displayed independently (currency cookie)

### Build 2 ✅
- [x] Enquiry reaches pipeline on submission
- [x] Enquiry can move through every stage
- [x] Owner can be assigned

### Build 3 ✅
- [x] Scans create structured records with score/band
- [x] Results display in admin with check-by-check breakdown
- [x] PDF reports generate with Oweru branding
- [x] Public scans create pipeline enquiries
- [x] Exclusion list checked before scanning

### Pending Verification
- [ ] 50-site batch testing
- [ ] Score comparison against hand-checked sample
- [ ] Robots compliance verified
- [ ] Personal-data controls verified
- [ ] Python scanner deployed and communicating with Laravel

---

*© 2026 Oweru International Ltd. All rights reserved.*
