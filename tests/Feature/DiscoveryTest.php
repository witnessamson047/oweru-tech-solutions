<?php

namespace Tests\Feature;

use App\Jobs\DiscoveryJob;
use App\Models\DiscoveryLead;
use App\Models\DiscoveryRun;
use App\Models\ScrapeTarget;
use App\Services\DiscoveryService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * DiscoveryService tests never touch the network: executeProbe() is
 * overridden to return the probe's --json contract (or throw), so the
 * queueing/dedupe/run-lifecycle logic is tested exactly as Laravel will
 * exercise it in production.
 */
class DiscoveryTest extends TestCase
{
    private function service(array $payload, ?string $throw = null): DiscoveryService
    {
        return new class($payload, $throw) extends DiscoveryService
        {
            public function __construct(
                private array $stubPayload,
                private ?string $stubError = null,
            ) {}

            protected function executeProbe(string $city, string $category, int $limit): array
            {
                if ($this->stubError !== null) {
                    throw new \RuntimeException($this->stubError);
                }

                return $this->stubPayload;
            }
        };
    }

    private function payload(array $websites, array $stats = []): array
    {
        return [
            'query' => ['city' => 'Dar es Salaam', 'category' => 'all', 'limit' => 300],
            'stats' => array_merge([
                'osm_records' => 332,
                'with_website_tag' => count($websites),
                'without_website' => 249,
                'invalid_website_urls' => 0,
                'skipped_non_business_links' => 0,
                'duplicate_hosts_merged' => 0,
                'unique_websites' => count($websites),
            ], $stats),
            'websites' => $websites,
            'no_website_sample' => [['name' => 'Kilimanjaro KEMPINSKI Hyatt Hotel', 'osm_type' => 'node']],
        ];
    }

    public function test_run_queues_discovered_websites_as_active_targets(): void
    {
        $payload = $this->payload([
            ['name' => 'CRDB Bank', 'url' => 'https://crdbbank.co.tz', 'host' => 'crdbbank.co.tz', 'osm_type' => 'node'],
            ['name' => 'Epi d\'Or', 'url' => 'https://epidor.co.tz', 'host' => 'epidor.co.tz', 'osm_type' => 'node'],
        ]);

        $run = $this->service($payload)->run('Dar es Salaam', 'all', 300, DiscoveryRun::SOURCE_ADMIN, 1);

        $run->refresh();
        $this->assertSame(DiscoveryRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(2, $run->queued_count);
        $this->assertSame(0, $run->skipped_count);
        $this->assertSame(332, $run->stats['osm_records']);
        $this->assertCount(1, $run->result['no_website']); // from the legacy sample key via the service's compat fallback

        $this->assertSame(2, ScrapeTarget::where('discovery_run_id', $run->id)->count());
        $this->assertSame(1, ScrapeTarget::where('url', 'https://crdbbank.co.tz/')->where('status', ScrapeTarget::STATUS_ACTIVE)->count());
    }

    public function test_run_skips_duplicate_hosts_and_unusable_urls(): void
    {
        $payload = $this->payload([
            ['name' => 'One', 'url' => 'https://dupe-site.example.com', 'host' => 'dupe-site.example.com', 'osm_type' => 'node'],
            ['name' => 'Two', 'url' => 'https://www.dupe-site.example.com/about', 'host' => 'www.dupe-site.example.com', 'osm_type' => 'node'],
            ['name' => 'Bad', 'url' => 'not a url at all', 'host' => '', 'osm_type' => 'node'],
        ]);

        $run = $this->service($payload)->run('Dar es Salaam');

        $run->refresh();
        $this->assertSame(1, $run->queued_count);
        $this->assertSame(2, $run->skipped_count);
    }

    public function test_run_never_queues_targets_that_are_already_in_the_queue(): void
    {
        $existing = ScrapeTarget::create(['url' => 'https://already-queued.example.com/', 'status' => ScrapeTarget::STATUS_ACTIVE]);

        $payload = $this->payload([
            ['name' => 'Already', 'url' => 'https://www.already-queued.example.com', 'host' => 'www.already-queued.example.com', 'osm_type' => 'node'],
        ]);

        $run = $this->service($payload)->run('Dar es Salaam');

        $run->refresh();
        $this->assertSame(0, $run->queued_count);
        $this->assertSame(1, $run->skipped_count);
        $this->assertNull($existing->fresh()->discovery_run_id);
    }

    public function test_failed_probe_is_recorded_on_the_run_and_queues_nothing(): void
    {
        $run = $this->service([], 'All Overpass endpoints failed. Last error: HTTP 504')->run('Arusha');

        $run->refresh();
        $this->assertSame(DiscoveryRun::STATUS_FAILED, $run->status);
        $this->assertStringContainsString('504', $run->error);
        $this->assertNotNull($run->finished_at);
        $this->assertSame(0, ScrapeTarget::where('discovery_run_id', $run->id)->count());
    }

    public function test_discovery_run_command_reports_results(): void
    {
        // Swap in a failing stub so the command test never hits the network.
        $this->swap(DiscoveryService::class, $this->service([], 'All Overpass endpoints failed (HTTP 504)'));

        $this->artisan('discovery:run', ['--city' => 'Dar es Salaam'])
            ->expectsOutputToContain('Discovery failed')
            ->assertExitCode(1);
    }

    public function test_admin_discovery_page_renders_and_records_runs(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->get(route('admin.discovery.index'))
            ->assertOk()
            ->assertSee('Website Discovery')
            ->assertSee('Discover');

        $payload = $this->payload([
            ['name' => 'Form Site', 'url' => 'https://form-site.example.com', 'host' => 'form-site.example.com', 'osm_type' => 'node'],
        ]);

        // Swap in the stub service for the controller's make() call.
        $this->swap(DiscoveryService::class, $this->service($payload));

        $this->actingAs($admin)->post(route('admin.discovery.store'), [
            'city' => 'Dar es Salaam',
            'category' => 'all',
            'limit' => 300,
        ])->assertRedirect()->assertSessionHas('success');

        $run = DiscoveryRun::where('source', DiscoveryRun::SOURCE_ADMIN)->firstOrFail();

        // Tests run on the sync queue connection, so the job already
        // executed inline during the POST (through the swapped stub).
        $run->refresh();
        $this->assertSame(DiscoveryRun::STATUS_COMPLETED, $run->status);
        $this->assertSame(1, ScrapeTarget::where('url', 'https://form-site.example.com/')->count());

        $this->actingAs($admin)->get(route('admin.discovery.index'))
            ->assertSee('Dar es Salaam')
            ->assertSee('completed');
    }

    public function test_admin_form_validates_city_and_category(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.discovery.store'), [
            'city' => '',
            'category' => 'not-a-category',
        ])->assertSessionHasErrors(['city', 'category']);
    }

    public function test_discovery_job_is_dispatched_by_the_form(): void
    {
        Queue::fake();

        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.discovery.store'), [
            'city' => 'Arusha',
            'category' => 'all',
        ])->assertRedirect()->assertSessionHas('success');

        Queue::assertPushed(DiscoveryJob::class, fn (DiscoveryJob $job) => $job->run->city === 'Arusha');
        $this->assertSame(1, DiscoveryRun::where('city', 'Arusha')->where('status', DiscoveryRun::STATUS_RUNNING)->count());
    }

    public function test_run_persists_no_website_leads_deduped_by_name_and_city(): void
    {
        $payload = $this->payload([]);
        $payload['no_website'] = [
            ['name' => 'Mama Itongo', 'osm_type' => 'node', 'lat' => -6.8, 'lon' => 39.3],
            ['name' => 'Chef Kile', 'osm_type' => 'node'],
        ];

        $service = $this->service($payload);
        $run1 = $service->run('Dar es Salaam');
        $run2 = $service->run('Dar es Salaam');

        // Same city twice: the outreach list is extended, never duplicated.
        $this->assertSame(2, DiscoveryLead::count());
        $this->assertSame(2, $run1->refresh()->leads_count);
        $this->assertSame(0, $run2->refresh()->leads_count);

        $lead = DiscoveryLead::where('business_name', 'Mama Itongo')->firstOrFail();
        $this->assertSame(-6.8, $lead->lat);
        $this->assertSame(39.3, $lead->lon);
        $this->assertSame('Dar es Salaam', $lead->city);
        $this->assertSame(DiscoveryLead::STATUS_NEW, $lead->status);
        $this->assertSame($run2->id, $lead->discovery_run_id); // attribution follows the freshest run
    }

    public function test_outreach_leads_page_renders_and_marks_contacted(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();

        DiscoveryLead::create(['business_name' => 'Safari Carnivour', 'city' => 'Dar es Salaam', 'category' => 'restaurant']);

        $this->actingAs($admin)->get(route('admin.discovery.leads'))
            ->assertOk()
            ->assertSee('No-Website Leads')
            ->assertSee('Safari Carnivour')
            ->assertSee('Mark contacted');

        $lead = DiscoveryLead::where('business_name', 'Safari Carnivour')->firstOrFail();

        $this->actingAs($admin)->post(route('admin.discovery.leads.contacted', $lead))
            ->assertRedirect()
            ->assertSessionHas('success');

        $lead->refresh();
        $this->assertSame(DiscoveryLead::STATUS_CONTACTED, $lead->status);
        $this->assertNotNull($lead->contacted_at);
    }

    public function test_dashboard_shows_discovery_stats_and_runs(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();

        $payload = $this->payload([
            ['name' => 'Dash Site', 'url' => 'https://dash-site.example.com', 'host' => 'dash-site.example.com', 'osm_type' => 'node'],
        ]);
        $payload['no_website'] = [['name' => 'Dash Lead', 'osm_type' => 'node']];

        $this->swap(DiscoveryService::class, $this->service($payload));

        $this->actingAs($admin)->post(route('admin.discovery.store'), [
            'city' => 'Dar es Salaam',
            'category' => 'all',
        ])->assertRedirect();

        (new DiscoveryJob(DiscoveryRun::latest()->first()))->handle();

        $this->actingAs($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Website Discovery')
            ->assertSee('Websites discovered this week')
            ->assertSee('No-Website Outreach')
            ->assertSee('Dar es Salaam'); // the run appears in the recent-runs panel

        $this->assertSame(1, ScrapeTarget::where('url', 'https://dash-site.example.com/')->count());
        $this->assertSame(1, DiscoveryLead::where('business_name', 'Dash Lead')->count());
    }
}
