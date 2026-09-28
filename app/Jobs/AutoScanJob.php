<?php

namespace App\Jobs;

use App\Models\Scan;
use App\Models\ScrapedBusiness;
use App\Models\Website;
use App\Services\ScannerClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Automatically health-scans a newly scraped business so its "what it lacks"
 * list (failed checks + mapped Oweru recommendations) builds itself with no
 * human clicking "Run Health Scan".
 *
 * This closes the automation loop: scrape → identify weaknesses → sales-ready
 * lead with concrete, evidence-backed recommendations.
 */
class AutoScanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    // Health scan worst case is well under 2 min; give headroom.
    public int $timeout = 300;

    public function __construct(
        public ScrapedBusiness $business,
    ) {}

    public function handle(): void
    {
        $business = $this->business->refresh();

        // Skip when a human already scanned this business.
        if ($business->website_id) {
            return;
        }

        $website = Website::firstOrCreate(
            ['url' => $business->website_url],
            [
                'business_name' => $business->business_name ?: $business->website_url,
                'status' => 'active',
                'exclusion_status' => 'active',
            ]
        );

        $scan = Scan::create([
            'website_id' => $website->id,
            'url' => $website->url,
            'status' => 'running',
            'source' => 'auto_scraper',
        ]);

        $result = ScannerClient::make()->runScan($scan);

        if ($result['ok']) {
            $business->update([
                'status' => 'scanned',
                'website_id' => $website->id,
            ]);

            Log::info('Auto-scan completed for scraped business', [
                'business_id' => $business->id,
                'website_id' => $website->id,
                'scan_id' => $scan->id,
                'score' => $scan->score,
            ]);
        } else {
            // Scan failed (engine offline, site down, …) — scan row stays
            // 'failed' for retry; business remains 'new' so it can be
            // scanned again later without losing its pipeline status.
            Log::warning('Auto-scan failed for scraped business', [
                'business_id' => $business->id,
                'scan_id' => $scan->id,
                'message' => $result['message'],
            ]);
        }
    }
}
