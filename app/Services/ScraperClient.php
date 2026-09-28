<?php

namespace App\Services;

use App\Jobs\AutoScanJob;
use App\Models\ScrapedBusiness;
use App\Models\ScrapeTarget;
use App\Services\ScrapeWatchdogService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScraperClient
{
    public function __construct(
        protected string $baseUrl,
        protected string $apiKey,
        // Worst-case scrape: robots (10s) + 4 pages × (2s rate-limit + 30s
        // fetch), plus up to ~30s headless-browser render per JS-shell page
        // (React/Vue SPAs) ≈ 260s, so the HTTP client must out-wait it all.
        protected int $timeout = 300,
        // Auto-scan dispatch counter for this client instance (per pass cap).
        protected int $autoScanQueued = 0,
    ) {}

    public static function make(): self
    {
        return new self(
            config('services.scanner.url', 'http://localhost:5000'),
            (string) config('services.scanner.api_key', ''),
        );
    }

    /**
     * Ask the Python scraper service to extract public business info from a URL
     * and persist it as a ScrapedBusiness record.
     *
     * @return array{ok: bool, message: string, business: ScrapedBusiness}
     */
    public function scrape(string $url): array
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'X-API-Key' => $this->apiKey,
                    'Accept' => 'application/json',
                ])
                ->post("{$this->baseUrl}/api/scraper/scrape", [
                    'url' => $url,
                ]);
        } catch (\Throwable $e) {
            Log::warning('Scraper service unreachable', ['url' => $url, 'error' => $e->getMessage()]);

            return [
                'ok' => false,
                'message' => 'Scraper service is currently unavailable. Is the Python engine running?',
                'business' => new ScrapedBusiness(['website_url' => $url]),
            ];
        }

        $data = $response->json();

        if (!$response->successful() || empty($data['success'])) {
            $message = $data['message'] ?? 'Scrape failed.';

        // Record the failure on any known business/target for monitoring
        $existing = ScrapedBusiness::where('website_url', $data['url'] ?? $url)->first();
        $existing?->update(['last_scrape_error' => $message, 'last_scraped_at' => now()]);

        // Watchdog: a previously-reachable site failing a scheduled re-scrape
        // is an outage signal worth alerting on.
        if ($existing) {
            try {
                app(ScrapeWatchdogService::class)->inspectFailure($existing);
            } catch (\Throwable $e) {
                Log::warning('Scrape watchdog failure check errored', ['url' => $url, 'error' => $e->getMessage()]);
            }
        }

            return [
                'ok' => false,
                'message' => $message,
                'business' => $existing ?? new ScrapedBusiness(['website_url' => $url]),
            ];
        }

        // Upsert on the unique website_url so re-scraping refreshes the record.
        // Business fields stay editable: only write a field when this scrape
        // actually extracted a value, so a partial re-scrape can't blank data.
        $payload = [
            'business_name' => $data['business_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'services' => $data['services'] ?? null,
            'about' => $data['about'] ?? null,
            'source_url' => $data['url'] ?? $url,
            'rating_avg' => $data['rating_avg'] ?? null,
            'rating_count' => $data['rating_count'] ?? null,
            'reviews' => $data['reviews'] ?? null,
            // Preliminary gaps/opportunities detected at scrape time (before
            // any health scan). Always refreshed on re-scrape.
            'preliminary_gaps' => is_array($data['preliminary_gaps'] ?? null) ? $data['preliminary_gaps'] : null,
            'last_scraped_at' => now(),
            'last_scrape_error' => null,
        ];

        // Upsert with trailing-slash flexibility: the same site may exist in
        // the table as https://abc.co.tz OR https://abc.co.tz/ (entered before
        // URL canonicalization). Never create a duplicate for the slash.
        $incomingUrl = $data['url'] ?? $url;
        $bareUrl = rtrim($incomingUrl, '/');
        $business = ScrapedBusiness::where('website_url', $incomingUrl)
            ->orWhere('website_url', $bareUrl)
            ->orWhere('website_url', $bareUrl . '/')
            ->first();

        if (! $business) {
            $business = new ScrapedBusiness(['website_url' => $incomingUrl]);
        } else {
            $business->website_url = $incomingUrl;
        }

        // Watchdog snapshot: what the fields looked like BEFORE this scrape,
        // so meaningful changes can be diffed and reported after the save.
        $before = $business->exists ? [
            'email' => $business->email,
            'phone' => $business->phone,
            'address' => $business->address,
            'services' => $business->services,
            'rating_avg' => $business->rating_avg,
            'rating_count' => $business->rating_count,
            'seen_review_texts' => $business->seen_review_texts ?? [],
            'has_scraped' => $business->last_scraped_at !== null,
            'scrape_count' => $business->scrape_count,
        ] : null;

        // Preserve workflow status: a business that is already a lead or was
        // scanned must not be reset to 'new' by a scheduled re-scrape.
        if (! $business->exists) {
            $payload['status'] = 'new';
        }

        foreach ($payload as $field => $value) {
            if ($value !== null && $value !== []) {
                $business->{$field} = $value;
            }
        }

        $business->save();

        $business->increment('scrape_count');

        // Watchdog: diff this scrape against the previous snapshot and record
        // / alert on meaningful changes (rating drops, new reviews, contact
        // changes, availability). Failures here must never break the scrape.
        try {
            app(ScrapeWatchdogService::class)->inspect($business, $before, [
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'services' => $data['services'] ?? null,
                'rating_avg' => $data['rating_avg'] ?? null,
                'rating_count' => $data['rating_count'] ?? null,
                'reviews' => $data['reviews'] ?? [],
            ]);
        } catch (\Throwable $e) {
            Log::warning('Scrape watchdog inspection errored', ['url' => $url, 'error' => $e->getMessage()]);
        }

        // Directory harvesting: when the scraped page looks like a business
        // directory, queue every discovered website as a new active target.
        $discovered = $this->discoverTargets($data);

        // Auto health-scan: newly discovered businesses are automatically
        // health-scanned so their weaknesses + mapped recommendations (the
        // "what it lacks" list) build themselves with zero human clicks.
        $this->maybeAutoScan($business);

        $message = 'Business information scraped and saved.';
        if ($discovered > 0) {
            $message .= " Directory page detected: {$discovered} new scrape target(s) queued.";
            Log::info('Scraper discovered new targets from directory page', [
                'source' => $url,
                'added' => $discovered,
            ]);
        }

        return ['ok' => true, 'message' => $message, 'business' => $business];
    }

    /**
     * Queue an AutoScanJob for this business when it was just created by the
     * scraper (never seen before). Capped per scrape run so a huge directory
     * burst cannot overwhelm the scanner engine.
     */
    protected function maybeAutoScan(ScrapedBusiness $business): void
    {
        if (! config('owers.scraper.auto_scan.enabled', true)) {
            return;
        }

        // Only brand-new businesses (first time seen by the scraper). If the
        // auto-scan fails (engine offline etc.) the business stays 'new' and
        // a later scrape retries it; a human scan marks website_id early.
        if (! $business->wasRecentlyCreated) {
            return;
        }
        if ($business->website_id || $business->status !== 'new') {
            return;
        }

        $cap = max(0, (int) config('owers.scraper.auto_scan.max_per_run', 5));
        if ($cap === 0 || $this->autoScanQueued >= $cap) {
            Log::info('Auto-scan cap reached for this scrape run; business left for a later pass', [
                'business_id' => $business->id,
            ]);

            return;
        }

        AutoScanJob::dispatch($business);
        $this->autoScanQueued++;

        Log::info('AutoScanJob queued for scraped business', ['business_id' => $business->id]);
    }

    /**
     * Auto-discovery: queue links reported by the Python engine as active
     * scrape targets. Python already filters social media, asset hosts and
     * document links and only reports pages that look like directories;
     * here we re-validate, dedupe by host, and cap the batch size.
     *
     * Links may come from classic external-link listing pages, or from
     * profile-based (JavaScript) directories where the engine samples the
     * directory's profile pages and returns the businesses' real websites.
     */
    protected function discoverTargets(array $data): int
    {
        if (! config('owers.scraper.discovery_enabled', true)) {
            return 0;
        }

        $links = $data['discovered_links'] ?? null;
        if (empty($links) || ! is_array($links)) {
            return 0;
        }

        // Default 5: profile-based discovery fetches directory profile pages
        // (rate-limited at 2s per host), so a small drip keeps each scheduled
        // pass fast while the re-scrape cycle gradually mines the directory.
        $limit = max(0, (int) config('owers.scraper.discovery_limit', 5));
        if ($limit === 0) {
            return 0;
        }

        // Host-level identity (www-stripped) so a directory that lists both
        // www.x.com and x.com queues the site once.
        $links = collect($links)
            ->map(fn ($link) => ScrapeTarget::normalizeUrl(is_string($link) ? $link : null))
            ->filter()
            ->unique(fn (string $url) => ScrapeTarget::hostKey($url))
            ->take($limit);

        $added = 0;
        $source = $data['url'] ?? 'unknown';

        // One query for all existing host keys (cheap even for big queues).
        $existingHostKeys = ScrapeTarget::query()
            ->get()
            ->map(fn (ScrapeTarget $t) => ScrapeTarget::hostKey($t->url))
            ->filter()
            ->flip();

        foreach ($links as $link) {
            $hostKey = ScrapeTarget::hostKey($link);

            if ($hostKey === null || $existingHostKeys->has($hostKey)) {
                continue;
            }

            ScrapeTarget::create([
                'url' => $link,
                'status' => ScrapeTarget::STATUS_ACTIVE,
                'notes' => "auto-discovered from {$source}",
            ]);
            $existingHostKeys->put($hostKey, true);
            $added++;
        }

        return $added;
    }
}
