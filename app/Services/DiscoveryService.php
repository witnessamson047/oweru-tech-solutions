<?php

namespace App\Services;

use App\Models\DiscoveryLead;
use App\Models\DiscoveryRun;
use App\Models\ScrapeTarget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;

/**
 * Runs the OSM/Overpass discovery probe (scanner/osm_discovery.py) and turns
 * its JSON output into scrape_targets rows.
 *
 * Design (agreed 2026-09-25): LARAVEL SHELLS OUT to the probe via Symfony
 * Process instead of going through the Flask engine. Discovery is an
 * occasional admin-triggered query of public Overpass infrastructure (30-120s),
 * not part of the 24/7 scrape/scan loop, so it must not depend on Flask being
 * up and needs no engine restart after probe edits.
 *
 * The probe's --json contract (stable, see osm_discovery.py):
 *   { query: {...}, stats: {osm_records, with_website_tag, unique_websites,
 *     without_website, invalid_website_urls, skipped_non_business_links,
 *     duplicate_hosts_merged}, websites: [{name,url,host,lat,lon,osm_type}],
 *     no_website_sample: [{name,osm_type}] }
 */
class DiscoveryService
{
    /** Extract ONLY the keys the DB needs from the probe payload. */
    private const STATS_KEYS = [
        'osm_records', 'with_website_tag', 'without_website',
        'invalid_website_urls', 'skipped_non_business_links',
        'duplicate_hosts_merged', 'unique_websites',
    ];

    public function __construct(
        private string $pythonBin,
        private string $scriptPath,
        private int $timeout,
    ) {}

    /**
     * Container-friendly factory: resolve paths from config. Like
     * ScraperClient::make() — required scalar constructor params mean the
     * container cannot auto-resolve this class, so callers build it here.
     * Honors an explicitly bound/swapped instance (tests). Always re-resolves
     * config so long-lived callers never cache stale settings.
     */
    public static function make(): self
    {
        if (app()->bound(self::class)) {
            return app(self::class);
        }

        return new self(
            (string) config('owers.discovery.python_bin'),
            (string) config('owers.discovery.script_path'),
            (int) config('owers.discovery.timeout', 150),
        );
    }

    /**
     * Execute one discovery run synchronously (artisan path). For the web
     * admin form use createRun() + DiscoveryJob — see the note on
     * Windows DNS quirk in executeProbe().
     */
    public function run(string $city, string $category = 'all', ?int $limit = null, string $source = DiscoveryRun::SOURCE_ADMIN, ?int $userId = null): DiscoveryRun
    {
        return $this->executeRun($this->createRun($city, $category, $limit, $source, $userId));
    }

    /** Create the run row (status=running). Called from the web request so
     * the run is recorded and visible BEFORE the job starts. */
    public function createRun(string $city, string $category = 'all', ?int $limit = null, string $source = DiscoveryRun::SOURCE_ADMIN, ?int $userId = null): DiscoveryRun
    {
        $city = trim($city);
        $category = trim($category) ?: 'all';
        $limit = $limit ?? (int) config('owers.discovery.default_limit', 300);

        return DiscoveryRun::create([
            'city' => $city,
            'category' => $category,
            'limit' => $limit,
            'status' => DiscoveryRun::STATUS_RUNNING,
            'source' => $source,
            'user_id' => $userId,
        ]);
    }

    /**
     * Execute a previously created run: probe Overpass, queue every usable
     * website URL into scrape_targets, persist no-website leads, record
     * stats. Never throws — failures land on the run row (status=failed)
     * and in the log; the caller decides what to show the user.
     */
    public function executeRun(DiscoveryRun $run): DiscoveryRun
    {
        $city = $run->city;
        $category = $run->category;
        $limit = $run->limit;

        try {
            $payload = $this->executeProbe($city, $category, $limit);
        } catch (\Throwable $e) {
            $run->update([
                'status' => DiscoveryRun::STATUS_FAILED,
                'error' => Str::limit($e->getMessage(), 5000),
                'finished_at' => now(),
            ]);

            Log::warning('Discovery probe failed', ['run_id' => $run->id, 'city' => $city, 'category' => $category, 'error' => $e->getMessage()]);

            return $run;
        }

        $queued = 0;
        $skipped = 0;
        $existingHostKeys = ScrapeTarget::query()
            ->get(['url'])
            ->map(fn (ScrapeTarget $t) => ScrapeTarget::hostKey($t->url))
            ->filter()
            ->flip();

        foreach ($payload['websites'] ?? [] as $site) {
            $outcome = $this->queueTarget($site, $run, $existingHostKeys);
            $queued += $outcome === 'queued' ? 1 : 0;
            $skipped += $outcome === 'skipped' ? 1 : 0;
        }

        // No-website businesses are the "we'll build you one" outreach list.
        // They cannot be scraped/scanned, so they are persisted as leads.
        $leadsAdded = 0;
        foreach (array_slice((array) ($payload['no_website'] ?? $payload['no_website_sample'] ?? []), 0, 500) as $lead) {
            $name = trim((string) ($lead['name'] ?? ''));

            if ($name === '') {
                continue;
            }

            // name+city identity: repeated runs of the same city extend and
            // refresh the outreach list instead of duplicating it.
            $leadModel = DiscoveryLead::firstOrNew(
                ['business_name' => $name, 'city' => $city],
            );

            if (! $leadModel->exists) {
                $leadsAdded++;
            }

            $leadModel->fill([
                'category' => $category,
                'osm_type' => $lead['osm_type'] ?? null,
                'lat' => $lead['lat'] ?? null,
                'lon' => $lead['lon'] ?? null,
                'discovery_run_id' => $run->id,
            ])->save();
        }

        $run->leads_count = $leadsAdded;

        $run->update([
            'status' => DiscoveryRun::STATUS_COMPLETED,
            'stats' => array_intersect_key(
                (array) ($payload['stats'] ?? []),
                array_flip(self::STATS_KEYS),
            ),
            'result' => [
                'websites' => array_slice((array) ($payload['websites'] ?? []), 0, 500),
                'no_website' => array_slice((array) ($payload['no_website'] ?? $payload['no_website_sample'] ?? []), 0, 500),
            ],
            'queued_count' => $queued,
            'skipped_count' => $skipped,
            'finished_at' => now(),
        ]);

        Log::info('Discovery run completed', [
            'run_id' => $run->id,
            'city' => $city,
            'category' => $category,
            'found' => count($payload['websites'] ?? []),
            'queued' => $queued,
            'skipped' => $skipped,
        ]);

        return $run;
    }

    /**
     * Queue ONE discovered website as a scrape target.
     * Returns 'queued' | 'skipped' (duplicate host or unusable URL).
     *
     * Mirrors ScrapeTargetController::store(): ScrapeTarget::normalizeUrl for
     * validation/canonicalization, hostKey for identity dedupe — the same
     * one-site-one-record rules the manual bulk-add uses. $existingHostKeys
     * is loaded once per run and updated in place as rows are created.
     */
    public function queueTarget(array $site, ?DiscoveryRun $run = null, ?\Illuminate\Support\Collection $existingHostKeys = null): string
    {
        $url = ScrapeTarget::normalizeUrl((string) ($site['url'] ?? ''));

        if ($url === null) {
            return 'skipped';
        }

        $hostKey = ScrapeTarget::hostKey($url);

        if ($hostKey === null) {
            return 'skipped';
        }

        $existingHostKeys ??= ScrapeTarget::query()
            ->get(['url'])
            ->map(fn (ScrapeTarget $t) => ScrapeTarget::hostKey($t->url))
            ->filter()
            ->flip();

        if ($existingHostKeys->has($hostKey)) {
            return 'skipped';
        }

        ScrapeTarget::create([
            'url' => $url,
            'status' => ScrapeTarget::STATUS_ACTIVE,
            'discovery_run_id' => $run?->id,
        ]);
        $existingHostKeys->put($hostKey, true);

        return 'queued';
    }

    /**
     * Run scanner/osm_discovery.py --json and parse its output.
     * Executable-failure and invalid-JSON paths throw RuntimeException with
     * the probe's stderr embedded — run() converts that into run.error.
     */
    protected function executeProbe(string $city, string $category, int $limit): array
    {
        $script = base_path($this->scriptPath);

        if (! is_file($script)) {
            throw new RuntimeException("Discovery probe not found at: {$script}");
        }

        $process = Process::fromShellCommandline(
            sprintf(
                '%s %s --city %s --category %s --limit %d --json',
                escapeshellarg($this->pythonBin),
                escapeshellarg($script),
                escapeshellarg($city),
                escapeshellarg($category),
                $limit,
            ),
            base_path('scanner'),
            $this->dnsOverrides(),
            timeout: $this->timeout,
        );

        $process->run();

        if (! $process->isSuccessful()) {
            $err = trim($process->getErrorOutput() ?: $process->getOutput());

            if ($err === '') {
                $err = "Discovery probe exited with code {$process->getExitCode()}";
            }

            // Python tracebacks bury the actual exception in the LAST line —
            // keep head (context) AND tail (the diagnosis) of stderr.
            $lastLine = collect(explode("\n", $err))->map(fn ($l) => trim($l))->filter()->last();

            throw new RuntimeException(Str::limit($err, 2000) . ($lastLine ? "\n… {$lastLine}" : ''));
        }

        $payload = json_decode($process->getOutput(), true);

        if (! is_array($payload) || ! isset($payload['websites']) || ! is_array($payload['websites'])) {
            throw new RuntimeException('Discovery probe returned invalid JSON (missing websites[]).');
        }

        return $payload;
    }

    /**
     * Resolve the probe's API hostnames with PHP (which resolves fine on this
     * machine) and hand the IPs to python via OWERU_DNS_OVERRIDES. Works
     * around a Windows quirk where processes spawned by the long-running
     * `php artisan serve` fail DNS with "[Errno 11003] getaddrinfo failed"
     * even though the same lookup succeeds from a fresh shell. The probe
     * patches socket.getaddrinfo — URL hostnames (and thus TLS/SNI) unchanged.
     */
    private function dnsOverrides(): array
    {
        $pairs = [];
        foreach (['nominatim.openstreetmap.org', 'overpass-api.de', 'overpass.kumi.systems'] as $host) {
            $ip = gethostbyname($host);

            if ($ip !== $host) { // gethostbyname returns the hostname on failure
                $pairs[] = "{$host}={$ip}";
            }
        }

        return $pairs === [] ? [] : ['OWERU_DNS_OVERRIDES' => implode(',', $pairs)];
    }
}
