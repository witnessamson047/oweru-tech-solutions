<?php

namespace App\Console\Commands;

use App\Models\DiscoveryRun;
use App\Services\DiscoveryService;
use Illuminate\Console\Command;

/**
 * Run the OSM/Overpass discovery probe and feed scrape_targets.
 *
 *   php artisan discovery:run --city="Dar es Salaam"                    # all categories
 *   php artisan discovery:run --city="Arusha" --category=hotel
 *   php artisan discovery:run --city="Mwanza" --limit=200 --stats
 *
 * NOTE: this command runs the probe synchronously (30-120s) — do NOT schedule
 * it; it exists for manual/admin use. Public Overpass is shared infrastructure.
 */
class DiscoveryRunCommand extends Command
{
    protected $signature = 'discovery:run
                            {--city= : City name, e.g. "Dar es Salaam" (required)}
                            {--category=all : Probe category (see osm_discovery.py --list-categories)}
                            {--limit= : Max OSM records to pull (default from config)}
                            {--stats : Show the probe stat counters after the run}';

    protected $description = 'Discover business websites via OpenStreetMap and queue them for scraping';

    public function handle(): int
    {
        $city = trim((string) $this->option('city'));

        if ($city === '') {
            $this->error('Please provide a city, e.g.  php artisan discovery:run --city="Dar es Salaam"');

            return self::FAILURE;
        }

        $category = trim((string) $this->option('category')) ?: 'all';
        $limit = $this->option('limit') !== null ? (int) $this->option('limit') : null;

        $this->info("Searching {$city} ({$category}) via OpenStreetMap …");

        // make(), not injection: scalar constructor params are not
        // container-resolvable (same as ScraperClient).
        $run = DiscoveryService::make()->run($city, $category, $limit, DiscoveryRun::SOURCE_COMMAND);

        if ($run->status === DiscoveryRun::STATUS_FAILED) {
            $this->error("Discovery failed: {$run->error}");

            return self::FAILURE;
        }

        $this->info("✓ Found {$run->stats['unique_websites']} website(s) — {$run->queued_count} queued, {$run->skipped_count} skipped (already in queue or invalid).");
        $this->line("  Run #{$run->id} recorded — scrape:run picks the new targets up automatically.");

        if ($this->option('stats')) {
            $this->line('Probe stats:');
            foreach ($run->stats as $key => $value) {
                $this->line("  {$key}: {$value}");
            }
        }

        return self::SUCCESS;
    }
}
