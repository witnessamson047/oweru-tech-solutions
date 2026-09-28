<?php

namespace Tests\Feature;

use App\Models\Scan;
use App\Models\ScannerCheck;
use App\Models\Website;
use App\Services\ScannerClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScannerWeightsTest extends TestCase
{
    private Website $website;

    private Scan $scan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::create([
            'business_name' => 'Weight Test Co',
            'url' => 'https://weight-test-' . uniqid() . '.example.com',
            'status' => 'active',
        ]);

        $this->scan = Scan::create([
            'website_id' => $this->website->id,
            'url' => $this->website->url,
            'status' => 'running',
            'source' => 'test',
        ]);
    }

    protected function tearDown(): void
    {
        $this->scan->results()->delete();
        $this->scan->delete();
        $this->website->delete();

        parent::tearDown();
    }

    public function test_scan_request_includes_weights_from_scanner_checks_table(): void
    {
        Http::fake([
            '*/api/scanner/scan' => Http::response(['success' => false, 'message' => 'stop here'], 400),
        ]);

        ScannerClient::make()->runScan($this->scan);

        $request = Http::recorded(fn ($req, $res) => str_contains($req->url(), '/api/scanner/scan'))[0][0];

        $checks = $request->data()['checks'];
        $this->assertIsArray($checks);
        $this->assertSame(ScannerCheck::count(), count($checks));

        // Every DB row must appear with its exact weight and enabled flag
        ScannerCheck::query()->each(function (ScannerCheck $check) use ($checks) {
            $match = collect($checks)->firstWhere('check_name', $check->name);
            $this->assertNotNull($match, "Missing check: {$check->name}");
            $this->assertSame((int) $check->weight, (int) $match['weight'], "Weight mismatch for {$check->name}");
            $this->assertSame((bool) $check->enabled, (bool) $match['enabled'], "Enabled mismatch for {$check->name}");
        });
    }

    public function test_admin_weight_change_is_sent_to_scanner(): void
    {
        // Admin re-weights "SSL Certificate Valid" from 10 to 25 via the UI/table
        ScannerCheck::where('name', 'SSL Certificate Valid')->update(['weight' => 25]);

        Http::fake([
            '*/api/scanner/scan' => Http::response(['success' => false, 'message' => 'stop here'], 400),
        ]);

        ScannerClient::make()->runScan($this->scan);

        $request = Http::recorded(fn ($req, $res) => str_contains($req->url(), '/api/scanner/scan'))[0][0];

        $ssl = collect($request->data()['checks'])->firstWhere('check_name', 'SSL Certificate Valid');
        $this->assertSame(25, (int) $ssl['weight'], 'Scanner must receive the admin-configured weight, not the hardcoded 10');
    }

    public function test_disabled_check_is_marked_disabled_in_payload(): void
    {
        ScannerCheck::where('name', 'Privacy Policy Page')->update(['enabled' => false]);

        Http::fake([
            '*/api/scanner/scan' => Http::response(['success' => false, 'message' => 'stop here'], 400),
        ]);

        ScannerClient::make()->runScan($this->scan);

        $request = Http::recorded(fn ($req, $res) => str_contains($req->url(), '/api/scanner/scan'))[0][0];

        $privacy = collect($request->data()['checks'])->firstWhere('check_name', 'Privacy Policy Page');
        $this->assertSame(false, (bool) $privacy['enabled']);
    }

    public function test_results_are_stored_from_scanner_response(): void
    {
        Http::fake([
            '*/api/scanner/scan' => Http::response([
                'success' => true,
                'score' => 70,
                'band' => 'Adequate',
                'results' => [
                    [
                        'check_name' => 'SSL Certificate Valid',
                        'area' => 'Security',
                        'weight' => 10,
                        'passed' => true,
                        'points' => 10,
                        'evidence' => 'HTTPS ok',
                        'finding_text' => 'Valid certificate.',
                        'consequence' => null,
                    ],
                    [
                        'check_name' => 'Privacy Policy Page',
                        'area' => 'Trust',
                        'weight' => 2,
                        'passed' => false,
                        'points' => 0,
                        'evidence' => 'No privacy policy',
                        'finding_text' => 'No privacy policy found.',
                        'consequence' => 'Reduced trust',
                    ],
                ],
            ], 200),
        ]);

        $result = ScannerClient::make()->runScan($this->scan);

        $this->assertTrue($result['ok']);
        $this->assertSame(70, $this->scan->refresh()->score);

        $stored = $this->scan->results()->get();
        $this->assertSame(2, $stored->count());

        $ssl = $stored->firstWhere('check_name', 'SSL Certificate Valid');
        $this->assertTrue((bool) $ssl->passed);
        $this->assertSame(10, (int) $ssl->points);
        $this->assertNotNull($ssl->check_id, 'Result should link to scanner_checks by name');

        $privacy = $stored->firstWhere('check_name', 'Privacy Policy Page');
        $this->assertFalse((bool) $privacy->passed);
        $this->assertSame(0, (int) $privacy->points);
    }
}
