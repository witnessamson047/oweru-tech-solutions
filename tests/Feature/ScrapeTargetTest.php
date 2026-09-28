<?php

namespace Tests\Feature;

use App\Models\ScrapeTarget;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScrapeTargetTest extends TestCase
{
    private array $createdTargetIds = [];

    protected function tearDown(): void
    {
        ScrapeTarget::whereIn('id', $this->createdTargetIds)->delete();

        parent::tearDown();
    }

    public function test_targets_page_renders_for_admin(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.scrape-targets.index'));

        $response->assertStatus(200);
        $response->assertSee('Scrape Targets');
        $response->assertSee('Add to Queue');
    }

    public function test_bulk_add_creates_targets(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $suffix = uniqid();

        $response = $this->actingAs($admin)->post(route('admin.scrape-targets.store'), [
            'urls' => "https://target-a-{$suffix}.example.com\nhttps://target-b-{$suffix}.example.com, target-c-{$suffix}.example.com",
        ]);

        $response->assertRedirect();

        $this->assertSame(3, ScrapeTarget::where('url', 'like', "%{$suffix}%")->count());

        ScrapeTarget::where('url', 'like', "%{$suffix}%")->get()
            ->each(fn ($t) => $this->createdTargetIds[] = $t->id);
    }

    public function test_bulk_add_does_not_duplicate_existing_targets(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $url = 'https://dupe-' . uniqid() . '.example.com';

        $target = ScrapeTarget::create(['url' => $url, 'status' => ScrapeTarget::STATUS_ACTIVE]);
        $this->createdTargetIds[] = $target->id;

        $this->actingAs($admin)->post(route('admin.scrape-targets.store'), [
            'urls' => $url,
        ]);

        $this->assertSame(1, ScrapeTarget::where('url', $url)->count());
    }

    public function test_invalid_urls_are_skipped(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $countBefore = ScrapeTarget::count();

        $this->actingAs($admin)->post(route('admin.scrape-targets.store'), [
            'urls' => "not a url at all !!! ???",
        ]);

        $this->assertSame($countBefore, ScrapeTarget::count());
    }

    public function test_pause_and_resume(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $target = ScrapeTarget::create([
            'url' => 'https://pause-' . uniqid() . '.example.com',
            'status' => ScrapeTarget::STATUS_ACTIVE,
        ]);
        $this->createdTargetIds[] = $target->id;

        $this->actingAs($admin)->post(route('admin.scrape-targets.pause', $target));
        $this->assertSame(ScrapeTarget::STATUS_PAUSED, $target->fresh()->status);

        // Paused targets are not due
        $this->assertSame(0, ScrapeTarget::where('id', $target->id)->due()->count());

        $this->actingAs($admin)->post(route('admin.scrape-targets.resume', $target));
        $this->assertSame(ScrapeTarget::STATUS_ACTIVE, $target->fresh()->status);
    }

    public function test_run_now_scrapes_and_updates_target(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $url = 'https://run-now-' . uniqid() . '.example.com';

        $target = ScrapeTarget::create(['url' => $url, 'status' => ScrapeTarget::STATUS_ACTIVE]);
        $this->createdTargetIds[] = $target->id;

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => true,
                'url' => $url,
                'business_name' => 'Run Now Ltd',
                'email' => 'info@runnow.example.com',
                'rating_avg' => 4.5,
                'rating_count' => 12,
                'reviews' => [
                    ['author' => 'Jane', 'text' => 'Great service, very professional team.', 'rating' => 5.0, 'source' => 'schema.org'],
                ],
            ], 200),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.scrape-targets.run-now', $target));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $target = $target->fresh();
        $this->assertSame(1, $target->scrape_count);
        $this->assertNotNull($target->last_scraped_at);
        $this->assertNull($target->last_error);
    }

    public function test_scrape_run_command_processes_due_targets(): void
    {
        $url = 'https://cmd-target-' . uniqid() . '.example.com';

        $target = ScrapeTarget::create(['url' => $url, 'status' => ScrapeTarget::STATUS_ACTIVE]);
        $this->createdTargetIds[] = $target->id;

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => true,
                'url' => $url,
                'business_name' => 'Cmd Ltd',
            ], 200),
        ]);

        $this->artisan('scrape:run')->assertSuccessful();

        $this->assertSame(1, $target->fresh()->scrape_count);
    }

    public function test_scrape_run_command_adds_targets_in_bulk(): void
    {
        $suffix = uniqid();

        $this->artisan('scrape:run', ['--add' => "https://bulk-{$suffix}.example.com"])
            ->assertSuccessful();

        // Stored canonical form carries a trailing slash on bare roots
        $target = ScrapeTarget::where('url', "https://bulk-{$suffix}.example.com/")->first();
        $this->createdTargetIds[] = $target->id ?? 0;

        $this->assertNotNull($target);
        $this->assertSame(ScrapeTarget::STATUS_ACTIVE, $target->status);
    }

    public function test_failed_target_is_retried_after_retry_interval(): void
    {
        $url = 'https://retry-' . uniqid() . '.example.com';

        $target = ScrapeTarget::create(['url' => $url, 'status' => ScrapeTarget::STATUS_ACTIVE]);
        $this->createdTargetIds[] = $target->id;

        // First scrape pass fails, second succeeds (Http::fake stubs merge,
        // first match wins — so use a sequence to vary the response).
        Http::fake([
            '*/api/scraper/scrape' => Http::sequence()
                ->push(['success' => false, 'message' => 'Website returned HTTP 500'], 422)
                ->push([
                    'success' => true,
                    'url' => $url,
                    'business_name' => 'Recovered Ltd',
                ], 200),
        ]);

        $this->artisan('scrape:run')->assertSuccessful();

        $target = $target->fresh();
        $this->assertNotNull($target->last_error, 'Failure should be recorded on the target');

        // Failing must NOT make the target due again immediately...
        $this->assertSame(
            0,
            ScrapeTarget::where('id', $target->id)->due()->count(),
            'Failed target should not retry instantly',
        );

        // ...but after the retry window (6h default) it must be due again
        $target->update(['last_scraped_at' => now()->subHours(7)]);

        $this->assertSame(
            1,
            ScrapeTarget::where('id', $target->id)->due()->count(),
            'Failed target should be retried after the retry interval',
        );

        // Second pass succeeds — error must be cleared
        $this->artisan('scrape:run')->assertSuccessful();

        $target = $target->fresh();
        $this->assertNull($target->last_error, 'Successful retry must clear last_error');
        $this->assertSame(2, $target->scrape_count);
    }

    public function test_successful_rescrape_does_not_reset_lead_status(): void
    {
        $url = 'https://lead-keep-' . uniqid() . '.example.com';

        $target = ScrapeTarget::create(['url' => $url, 'status' => ScrapeTarget::STATUS_ACTIVE]);
        $this->createdTargetIds[] = $target->id;

        $business = \App\Models\ScrapedBusiness::create([
            'business_name' => 'Lead Biz',
            'website_url' => $url,
            'source_url' => $url,
            'email' => 'old@leadkeep.example.com',
            'status' => 'lead',
        ]);

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => true,
                'url' => $url,
                'business_name' => 'Lead Biz',
                'email' => 'new@leadkeep.example.com',
            ], 200),
        ]);

        $this->artisan('scrape:run')->assertSuccessful();

        $business = $business->fresh();
        $this->assertSame('lead', $business->status, 'Re-scrape must not reset a lead back to new');
        $this->assertSame('new@leadkeep.example.com', $business->email, 'Fresh scrape data should update the record');
    }
}
