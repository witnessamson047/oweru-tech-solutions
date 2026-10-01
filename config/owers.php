<?php

// Oweru business configuration — invoicing, deposits, and auto-scraper cadence.
return [

    /*
    |--------------------------------------------------------------------------
    | Generated-file storage (PDF reports + receipts)
    |--------------------------------------------------------------------------
    | Default (null) keeps everything in storage/app as always. On serverless
    | hosts with a read-only deployment filesystem (Vercel), set
    | OWERU_STORAGE_DIR to a writable path (e.g. /tmp) — see LocalPath.
    */

    'storage' => [
        'dir' => env('OWERU_STORAGE_DIR'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Invoicing
    |--------------------------------------------------------------------------
    */

    'invoice' => [
        // Share of the total the customer must pay before work starts.
        'deposit_percent' => env('OWERU_DEPOSIT_PERCENT', 50),

        // Days until the deposit payment is due after an invoice is issued.
        'deposit_due_days' => env('OWERU_DEPOSIT_DUE_DAYS', 7),

        // Prefix for invoice numbers: OWU-INV-2026-0001
        'number_prefix' => 'OWU-INV',

        // Overdue-deposit reminders: email the customer every N days,
        // at most M times per invoice (0 disables reminders).
        'reminder_interval_days' => env('OWERU_REMINDER_INTERVAL_DAYS', 3),
        'max_reminders' => env('OWERU_MAX_REMINDERS', 4),
    ],

    /*
    |--------------------------------------------------------------------------
    | External measurement API (component 4: External API)
    |--------------------------------------------------------------------------
    | Real-world mobile performance data our own HTTP-based scanner cannot
    | measure. Google PageSpeed Insights returns Lighthouse metrics (FCP, LCP,
    | TBT, CLS, performance score) for the scanned URL. All failures are
    | log-only: a PSI outage never fails a scan.
    */

    'external' => [
        'pagespeed' => [
            'enabled' => filter_var(env('OWERU_PAGESPEED_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'api_key' => env('OWERU_PAGESPEED_API_KEY'),
            'base_url' => env('OWERU_PAGESPEED_BASE_URL', 'https://www.googleapis.com/pagespeedonline/v5'),
            // Free tier without a key works but is heavily rate-limited.
            'timeout' => env('OWERU_PAGESPEED_TIMEOUT', 60),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI interpretation API (component 5: AI API)
    |--------------------------------------------------------------------------
    | The scanner produces evidence; AI turns that evidence into a plain-
    | language executive summary, prioritised next actions and a pitch email.
    | The business consequence + recommended Oweru service NEVER come from AI —
    | those stay in the recommendations mapping table. AI only re-explains.
    | Without an API key the service falls back to a deterministic summary
    | built from the structured findings, so the pipeline works keyless.
    */

    'ai' => [
        'enabled' => filter_var(env('OWERU_AI_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'api_key' => env('OWERU_AI_API_KEY'),
        'base_url' => env('OWERU_AI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OWERU_AI_MODEL', 'gpt-4o-mini'),
        'timeout' => env('OWERU_AI_TIMEOUT', 45),
        // Max failed checks fed to the model (keeps prompt + cost bounded).
        'max_findings' => env('OWERU_AI_MAX_FINDINGS', 10),
    ],

    'company' => [
        'name' => 'Oweru International Ltd',
        'tagline' => 'Tech Solutions',
        'email' => env('OWERU_COMPANY_EMAIL', 'info@oweru.co.tz'),
        'phone' => env('OWERU_COMPANY_PHONE', '+255 711 890 764'),
        'location' => 'Dar es Salaam, Tanzania',
    ],

    /*
    |--------------------------------------------------------------------------
    | Website discovery (OSM/Overpass -> scrape_targets)
    |--------------------------------------------------------------------------
    | Runs scanner/osm_discovery.py (stdlib-only probe) via Symfony Process.
    | Deliberately a shell-out, NOT a Flask endpoint: discovery is an
    | occasional admin-triggered public-Overpass query, not part of the 24/7
    | scrape/scan engine loop, so it must not depend on Flask being up.
    */

    'discovery' => [
        // Windows: C:\python312\python.exe (Python 3.12 is not on PATH).
        'python_bin' => env('OWERU_DISCOVERY_PYTHON', 'C:\\python312\\python.exe'),

        // Probe location, relative to the project root.
        'script_path' => env('OWERU_DISCOVERY_SCRIPT', 'scanner/osm_discovery.py'),

        // Worst-case probe wall time: geocode 30s + 2 endpoints x 2 attempts
        // x 100s + backoff. Live runs finish in well under 2 minutes; this
        // only matters when the shared public instances are having a bad day.
        'timeout' => env('OWERU_DISCOVERY_TIMEOUT', 480),

        // Default/max OSM records per run (--limit of the probe).
        'default_limit' => env('OWERU_DISCOVERY_DEFAULT_LIMIT', 300),
        'max_limit' => env('OWERU_DISCOVERY_MAX_LIMIT', 1000),

        // Admin form dropdown. 'all' = every named business carrying a
        // website tag (the proven high-yield mode: 262 Dar sites in one run).
        'categories' => [
            'all' => 'All businesses (recommended)',
            'hotel' => 'Hotels',
            'guesthouse' => 'Guest houses',
            'restaurant' => 'Restaurants',
            'cafe' => 'Cafes',
            'bar' => 'Bars & pubs',
            'supermarket' => 'Supermarkets',
            'school' => 'Schools',
            'hospital' => 'Hospitals & clinics',
            'pharmacy' => 'Pharmacies',
            'bank' => 'Banks',
            'travel' => 'Travel agencies',
            'office' => 'Offices',
            'garage' => 'Garages',
            'salon' => 'Salons',
        ],

        // Typeahead suggestions for the Location box (non-technical staff).
        'city_suggestions' => [
            'Dar es Salaam', 'Arusha', 'Mwanza', 'Dodoma', 'Mbeya', 'Tanga',
            'Morogoro', 'Zanzibar', 'Moshi', 'Songea', 'Musoma', 'Iringa',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Auto-scraper (24/7 background scraping)
    |--------------------------------------------------------------------------
    */

    'scraper' => [
        // Re-scrape every active target at most this often.
        'refresh_days' => env('OWERU_SCRAPER_REFRESH_DAYS', 7),

        // How many targets one scrape:run pass processes.
        'batch_size' => env('OWERU_SCRAPER_BATCH_SIZE', 10),

        // A target whose last scrape failed is retried after this many hours
        // instead of waiting the full refresh interval.
        'retry_hours' => env('OWERU_SCRAPER_RETRY_HOURS', 6),

        // Directory harvesting: when a scraped page looks like a business
        // directory (many distinct external links), its outbound business
        // links are auto-queued as new active targets, which are then
        // scraped automatically on subsequent scheduler passes.
        'discovery_enabled' => filter_var(env('OWERU_SCRAPER_DISCOVERY', true), FILTER_VALIDATE_BOOLEAN),

        // Maximum new targets queued from a single directory page (0 = no cap).
        'discovery_limit' => env('OWERU_SCRAPER_DISCOVERY_LIMIT', 20),

        // Watchdog: compare every re-scrape against the previous snapshot and
        // record + alert on meaningful changes (rating drops, new reviews,
        // contact changes, site down). See ScrapeWatchdogService.
        'watchdog' => [
            'enabled' => filter_var(env('OWERU_SCRAPER_WATCHDOG', true), FILTER_VALIDATE_BOOLEAN),

            // Alert when the average rating falls by at least this many stars.
            'rating_drop_threshold' => env('OWERU_WATCHDOG_RATING_DROP', 0.5),

            // A review rated at or below this is treated as negative (hot alert).
            'bad_review_threshold' => env('OWERU_WATCHDOG_BAD_REVIEW', 3),

            // At most one staff alert per business per this many hours,
            // however many hot events fire inside the window.
            'notify_cooldown_hours' => env('OWERU_WATCHDOG_COOLDOWN_HOURS', 24),
        ],

        // Auto health-scan: every newly scraped business is automatically
        // health-scanned so its weaknesses + mapped Oweru recommendations
        // (the "what it lacks" list) build themselves with zero clicks.
        // Scanned results also surface on the dashboard (low-score prospects).
        'auto_scan' => [
            'enabled' => filter_var(env('OWERU_AUTO_SCAN', true), FILTER_VALIDATE_BOOLEAN),

            // Cap per scrape:run pass so one big directory burst cannot
            // overwhelm the scanner engine.
            'max_per_run' => env('OWERU_AUTO_SCAN_MAX_PER_RUN', 5),
        ],
    ],

];
