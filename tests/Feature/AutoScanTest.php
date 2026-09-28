<?php

namespace Tests\Feature;

use App\Jobs\AutoScanJob;
use App\Models\Scan;
use App\Models\ScrapedBusiness;
use App\Models\Website;
use App\Services\ScraperClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AutoScanTest extends TestCase
{
    protected function tearDown(): void
    {
        ScrapedBusiness::where('website_url', 'like', '%.example.com')->delete();
        Website::where('url', 'like', '%.example.com')->delete();

        parent::tearDown();
    }

    protected function fakeScrape(array $payload = []): void
    {
        Http::fake([
            '*/api/scraper/scrape' => Http::response(array_merge([
                'success' => true,
                'url' => 'https://autoscanned.example.com',
                'business_name' => 'Auto Scanned Ltd',
                'email' => 'info@autoscanned.example.com',
            ], $payload), 200),
        ]);
    }

    protected function fakeScanner(array $results): void
    {
        Http::fake([
            '*/api/scanner/scan' => Http::response([
                'success' => true,
                'score' => 45,
                'results' => $results,
            ], 200),
        ]);
    }

    public function test_new_business_gets_website_scan_and_recommendation_link(): void
    {
        Queue::fake();

        $this->fakeScrape();

        $result = ScraperClient::make()->scrape('https://autoscanned.example.com');
        $this->assertTrue($result['ok']);

        Queue::assertPushed(AutoScanJob::class);

        // Now run the job for real with the scanner faked. 'SSL Certificate
        // Valid' is a seeded Recommendation check_name, so the failed result
        // must map to it via ScanResult::recommendation.
        $this->fakeScanner([
            ['check_name' => 'SSL Certificate Valid', 'area' => 'Security', 'passed' => false, 'points' => 10, 'finding_text' => 'No HTTPS', 'consequence' => 'Security warnings'],
            ['check_name' => 'Responsive Layout', 'area' => 'Mobile', 'passed' => true, 'points' => 5],
        ]);

        $business = ScrapedBusiness::where('website_url', 'https://autoscanned.example.com')->firstOrFail();
        (new AutoScanJob($business))->handle();

        $business->refresh();
        $this->assertSame('scanned', $business->status);
        $this->assertNotNull($business->website_id);

        $website = Website::find($business->website_id);
        $this->assertSame('https://autoscanned.example.com', $website->url);

        $scan = Scan::where('website_id', $website->id)->first();
        $this->assertNotNull($scan);
        $this->assertSame('completed', $scan->status);
        $this->assertSame(45, $scan->score);
        $this->assertSame('auto_scraper', $scan->source);

        // The recommendation link: failed check_name matches a Recommendation
        // (seeded) by check_name via the ScanResult::recommendation relation.
        $failed = $scan->results()->where('passed', false)->get();
        $this->assertGreaterThan(0, $failed->count());
        $this->assertTrue($failed->contains(fn ($r) => $r->recommendation !== null),
            'at least one failed check should map to a seeded recommendation');
    }

    public function test_rescraped_business_is_not_auto_scanned_again(): void
    {
        Queue::fake();

        $business = ScrapedBusiness::create([
            'business_name' => 'Existing Ltd',
            'website_url' => 'https://existing.example.com',
            'source_url' => 'https://existing.example.com',
            'status' => 'new',
        ]);

        $this->fakeScrape(['url' => $business->website_url, 'business_name' => 'Existing Ltd']);

        ScraperClient::make()->scrape($business->website_url);

        Queue::assertNotPushed(AutoScanJob::class);
    }

    public function test_auto_scan_skips_business_already_linked_to_website(): void
    {
        $website = Website::create([
            'business_name' => 'Linked Ltd',
            'url' => 'https://linked.example.com',
            'status' => 'active',
            'exclusion_status' => 'active',
        ]);

        $business = ScrapedBusiness::create([
            'business_name' => 'Linked Ltd',
            'website_url' => 'https://linked.example.com',
            'source_url' => 'https://linked.example.com',
            'status' => 'scanned',
            'website_id' => $website->id,
        ]);

        (new AutoScanJob($business))->handle();

        $this->assertSame(0, Scan::where('website_id', $website->id)->count());
    }

    public function test_auto_scan_disabled_by_config(): void
    {
        Queue::fake();
        config(['owers.scraper.auto_scan.enabled' => false]);

        $this->fakeScrape();

        ScraperClient::make()->scrape('https://autoscanned.example.com');

        Queue::assertNotPushed(AutoScanJob::class);
    }

    public function test_auto_scan_cap_per_run_is_respected(): void
    {
        Queue::fake();
        config(['owers.scraper.auto_scan.max_per_run' => 2]);

        // Http::fake stubs merge (first match wins), so 4 distinct scrape
        // responses need a sequence.
        $sequence = Http::sequence();
        foreach (['a', 'b', 'c', 'd'] as $slug) {
            $sequence->push([
                'success' => true,
                'url' => "https://cap-{$slug}.example.com",
                'business_name' => "Cap {$slug}",
            ], 200);
        }
        Http::fake(['*/api/scraper/scrape' => $sequence]);

        $client = ScraperClient::make();

        foreach (['a', 'b', 'c', 'd'] as $slug) {
            $client->scrape("https://cap-{$slug}.example.com");
        }

        Queue::assertPushed(AutoScanJob::class, 2);
    }

    public function test_failed_auto_scan_leaves_business_scannable(): void
    {
        $business = ScrapedBusiness::create([
            'business_name' => 'Fail Ltd',
            'website_url' => 'https://fail-scan.example.com',
            'source_url' => 'https://fail-scan.example.com',
            'status' => 'new',
        ]);

        // Scanner engine unreachable
        Http::fake([
            '*/api/scanner/scan' => function () {
                throw new \Exception('connection refused');
            },
        ]);

        (new AutoScanJob($business))->handle();

        $business->refresh();
        $this->assertSame('new', $business->status, 'business stays new so a later pass retries');
        $this->assertNull($business->website_id);
        $this->assertSame(1, Scan::where('status', 'failed')->count());
    }
}
