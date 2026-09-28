<?php

namespace App\Console\Commands;

use App\Models\ScrapeTarget;
use App\Services\ScraperClient;
use Illuminate\Console\Command;

class ScrapeRunCommand extends Command
{
    /**
     * Adds scrape targets (bulk, newline/comma separated) or processes due
     * targets. Designed to run every few minutes from the scheduler; the
     * scheduler runs 24/7 as long as `php artisan schedule:work` (or cron)
     * is alive.
     *
     * Usage:
     *   php artisan scrape:run                 # process up to --batch due targets
     *   php artisan scrape:run --add="url1 url2 url3"
     *   php artisan scrape:run --stats         # show queue summary
     */
    protected $signature = 'scrape:run
                            {--add= : Bulk-add targets (URLs separated by spaces, commas or newlines)}
                            {--batch= : Override how many targets to process this pass}
                            {--days= : Override the re-scrape interval in days}
                            {--stats : Only show queue statistics}';

    protected $description = 'Process due scrape targets 24/7 (or bulk-add new targets with --add=)';

    public function handle(): int
    {
        if ($add = $this->option('add')) {
            return $this->addTargets($add);
        }

        if ($this->option('stats')) {
            return $this->showStats();
        }

        return $this->processDue(ScraperClient::make());
    }

    protected function addTargets(string $raw): int
    {
        $urls = preg_split('/[\s,]+/', trim($raw)) ?: [];
        $urls = array_values(array_unique(array_filter($urls)));

        $added = 0;
        $skipped = 0;

        foreach ($urls as $raw) {
            $url = ScrapeTarget::normalizeUrl($raw);

            if (! $url) {
                $this->warn("  ✗ invalid: {$raw}");
                $skipped++;
                continue;
            }

            $target = ScrapeTarget::firstOrCreate(
                ['url' => $url],
                ['status' => ScrapeTarget::STATUS_ACTIVE]
            );

            if ($target->wasRecentlyCreated) {
                $added++;
            } else {
                $target->update(['status' => ScrapeTarget::STATUS_ACTIVE]);
                $skipped++;
            }
        }

        $this->info("✓ {$added} target(s) added, {$skipped} skipped (already queued or invalid).");

        return self::SUCCESS;
    }

    protected function showStats(): int
    {
        $stats = [
            'active' => ScrapeTarget::where('status', ScrapeTarget::STATUS_ACTIVE)->count(),
            'paused' => ScrapeTarget::where('status', ScrapeTarget::STATUS_PAUSED)->count(),
            'done' => ScrapeTarget::where('status', ScrapeTarget::STATUS_DONE)->count(),
            'due now' => ScrapeTarget::due((int) $this->option('days') ?: config('owers.scraper.refresh_days'))->count(),
        ];

        $this->info('Scraper queue:');
        foreach ($stats as $label => $count) {
            $this->line("  {$label}: {$count}");
        }

        return self::SUCCESS;
    }

    protected function processDue(ScraperClient $client): int
    {
        $days = (int) ($this->option('days') ?: config('owers.scraper.refresh_days', 7));
        $batch = (int) ($this->option('batch') ?: config('owers.scraper.batch_size', 10));

        $targets = ScrapeTarget::due($days)->orderBy('last_scraped_at')->limit($batch)->get();

        if ($targets->isEmpty()) {
            $this->line('No scrape targets are due.');

            return self::SUCCESS;
        }

        $ok = 0;
        $fail = 0;

        foreach ($targets as $target) {
            $this->line("→ Scraping {$target->url} …");

            $result = $client->scrape($target->url);

            $target->update([
                'last_scraped_at' => now(),
                'scrape_count' => $target->scrape_count + 1,
                'last_error' => $result['ok'] ? null : $result['message'],
            ]);

            if ($result['ok']) {
                $ok++;
                $this->info("  ✓ {$result['message']}");
            } else {
                $fail++;
                $this->warn("  ✗ {$result['message']}");
            }
        }

        $this->info("Done: {$ok} scraped, {$fail} failed, " . ($targets->count() - $ok - $fail) . ' skipped.');

        return self::SUCCESS;
    }
}
