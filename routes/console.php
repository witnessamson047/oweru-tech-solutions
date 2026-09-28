<?php

use Illuminate\Support\Facades\Schedule;

// Pipeline hygiene: remind staff about enquiries stuck in one stage
Schedule::command('enquiries:send-stage-reminders --days=3')->weekdays()->dailyAt('09:00');

// 24/7 auto-scraper: process due scrape targets every 5 minutes.
// Each pass scrapes a small batch (config owers.scraper.batch_size) and each
// target is re-scraped at most once per refresh interval (default 7 days).
// Requires `php artisan schedule:work` running in the background.
Schedule::command('scrape:run')->everyFiveMinutes()->withoutOverlapping();

// Overdue-deposit reminders: customers whose invoice deposit passed its due
// date get a friendly email every N days (default 3), max M per invoice (default 4).
Schedule::command('invoices:send-overdue-reminders')->dailyAt('10:00');

// Payment reconciliation: settle completed payments whose callback/IPN never
// landed (receipt fires then), and re-check pending payments with PesaPal.
// Cheap when there is nothing to do; PesaPal lookups only hit payments
// pending for over an hour.
Schedule::command('payments:reconcile')->everyTenMinutes()->withoutOverlapping();
