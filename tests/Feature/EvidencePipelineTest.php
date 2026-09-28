<?php

namespace Tests\Feature;

use App\Jobs\PostScanJob;
use App\Models\Scan;
use App\Models\Website;
use App\Services\AiInsightService;
use App\Services\PageSpeedService;
use App\Services\ScannerClient;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EvidencePipelineTest extends TestCase
{
    private Website $website;

    private Scan $scan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->website = Website::create([
            'business_name' => 'Evidence Test Co',
            'url' => 'https://evidence-' . uniqid() . '.example.com',
            'status' => 'active',
        ]);

        $this->scan = Scan::create([
            'website_id' => $this->website->id,
            'url' => $this->website->url,
            'status' => 'completed',
            'score' => 45,
            'band' => 'Weak',
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

    private function seedFindings(): void
    {
        ScannerClient::make()->storeResults($this->scan, [
            [
                'check_name' => 'SSL Certificate Valid',
                'area' => 'Security',
                'weight' => 10,
                'passed' => false,
                'points' => 0,
                'evidence' => 'HTTPS: false',
                'finding_text' => 'No HTTPS.',
                'consequence' => 'Visitors see security warnings.',
            ],
            [
                'check_name' => 'Responsive Layout',
                'area' => 'Mobile',
                'weight' => 8,
                'passed' => false,
                'points' => 0,
                'evidence' => 'No viewport meta tag',
                'finding_text' => 'Horizontal scrolling on mobile.',
                'consequence' => 'Mobile visitors struggle.',
            ],
        ]);
    }

    public function test_pagespeed_metrics_captured_and_stored(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'lighthouseResult' => [
                    'audits' => [
                        'first-contentful-paint' => ['numericValue' => 1200],
                        'largest-contentful-paint' => ['numericValue' => 3400],
                        'total-blocking-time' => ['numericValue' => 250],
                        'cumulative-layout-shift' => ['numericValue' => 0.05],
                    ],
                    'categories' => ['performance' => ['score' => 0.62]],
                ],
            ], 200),
        ]);

        $payload = PageSpeedService::make()->measure($this->scan);

        $this->assertNotNull($payload);
        $this->assertSame(62, $payload['performance_score']);
        $this->assertSame(1.2, $payload['metrics']['fcp_s']);
        $this->assertSame(3.4, $payload['metrics']['lcp_s']);
        $this->assertSame(0.25, $payload['metrics']['tbt_s']);
        $this->assertSame(0.05, $payload['metrics']['cls']);

        $this->scan->refresh();
        $this->assertSame(62, $this->scan->pagespeed['performance_score']);
    }

    public function test_pagespeed_failure_is_log_only(): void
    {
        Http::fake([
            'www.googleapis.com/*' => Http::response(['error' => ['message' => 'quota']], 429),
        ]);

        $payload = PageSpeedService::make()->measure($this->scan);

        $this->assertNull($payload);
        $this->scan->refresh();
        $this->assertNull($this->scan->pagespeed);
    }

    public function test_pagespeed_disabled_by_config(): void
    {
        config(['owers.external.pagespeed.enabled' => false]);

        $payload = PageSpeedService::make()->measure($this->scan);

        $this->assertNull($payload);
        $this->scan->refresh();
        $this->assertNull($this->scan->pagespeed);
    }

    public function test_pagespeed_band_helper(): void
    {
        $this->assertSame('Good', PageSpeedService::band(95));
        $this->assertSame('Needs improvement', PageSpeedService::band(60));
        $this->assertSame('Poor', PageSpeedService::band(20));
        $this->assertSame('Unknown', PageSpeedService::band(null));
    }

    public function test_ai_insight_from_api_is_parsed_and_stored(): void
    {
        $this->seedFindings();
        config(['owers.ai.api_key' => 'test-key']);

        Http::fake([
            'api.openai.com/*' => Http::response([
                'choices' => [
                    ['message' => ['content' => json_encode([
                        'summary' => 'The site loses mobile visitors immediately.',
                        'next_actions' => ['Add HTTPS', 'Improve responsive design'],
                        'pitch_email' => 'Hello, we can help...',
                    ])]],
                ],
            ], 200),
        ]);

        $insight = AiInsightService::make()->interpret($this->scan);

        $this->assertSame('ai', $insight['source']);
        $this->assertSame('The site loses mobile visitors immediately.', $insight['summary']);
        $this->assertSame(['Add HTTPS', 'Improve responsive design'], $insight['next_actions']);

        $this->scan->refresh();
        $this->assertSame('ai', $this->scan->ai_insight['source']);
    }

    public function test_ai_fallback_used_when_no_api_key(): void
    {
        $this->seedFindings();
        config(['owers.ai.api_key' => null]);

        $insight = AiInsightService::make()->interpret($this->scan);

        $this->assertSame('fallback', $insight['source']);
        $this->assertStringContainsString('2 health checks failed', $insight['summary']);
        $this->assertNotEmpty($insight['next_actions']);
        $this->assertStringContainsString('Oweru Tech Solutions', $insight['pitch_email']);

        $this->scan->refresh();
        $this->assertSame('fallback', $this->scan->ai_insight['source']);
    }

    public function test_ai_fallback_used_when_api_fails(): void
    {
        $this->seedFindings();
        config(['owers.ai.api_key' => 'test-key']);

        Http::fake([
            'api.openai.com/*' => Http::response(['error' => ['message' => 'overloaded']], 503),
        ]);

        $insight = AiInsightService::make()->interpret($this->scan);

        $this->assertSame('fallback', $insight['source']);
        $this->scan->refresh();
        $this->assertSame('fallback', $this->scan->ai_insight['source']);
    }

    public function test_post_scan_job_runs_enrichment(): void
    {
        Queue::fake();
        $this->seedFindings();

        Http::fake([
            'www.googleapis.com/*' => Http::response([
                'lighthouseResult' => [
                    'audits' => [
                        'first-contentful-paint' => ['numericValue' => 900],
                        'largest-contentful-paint' => ['numericValue' => 2100],
                        'total-blocking-time' => ['numericValue' => 100],
                        'cumulative-layout-shift' => ['numericValue' => 0.02],
                    ],
                    'categories' => ['performance' => ['score' => 0.81]],
                ],
            ], 200),
            'api.openai.com/*' => Http::response(['error' => ['message' => 'x']], 500),
        ]);

        (new PostScanJob($this->scan))->handle();

        $this->scan->refresh();
        $this->assertNotNull($this->scan->pagespeed, 'PSI metrics must be captured during post-scan');
        $this->assertNotNull($this->scan->ai_insight, 'AI insight (fallback ok) must be stored during post-scan');
        $this->assertSame('fallback', $this->scan->ai_insight['source']);
    }

    public function test_scans_with_no_failed_checks_skip_ai_insight(): void
    {
        ScannerClient::make()->storeResults($this->scan, [
            [
                'check_name' => 'SSL Certificate Valid',
                'area' => 'Security',
                'weight' => 10,
                'passed' => true,
                'points' => 10,
                'evidence' => 'ok',
                'finding_text' => 'ok',
                'consequence' => null,
            ],
        ]);

        $insight = AiInsightService::make()->interpret($this->scan);

        $this->assertNull($insight);
        $this->scan->refresh();
        $this->assertNull($this->scan->ai_insight);
    }
}
