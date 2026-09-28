<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\ScrapedBusiness;
use App\Models\ScrapeWatchEvent;
use App\Services\ScraperClient;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScrapeWatchdogTest extends TestCase
{
    protected function tearDown(): void
    {
        // Keep the dev DB clean when tests run outside the :memory: config.
        Notification::where('type', 'watchdog_alert')->delete();
        ScrapeWatchEvent::query()->delete();
        ScrapedBusiness::where('website_url', 'like', '%.example.com')->delete();

        parent::tearDown();
    }

    protected function makeBusiness(array $attributes = []): ScrapedBusiness
    {
        return ScrapedBusiness::create(array_merge([
            'business_name' => 'Watched Cafe',
            'website_url' => 'https://watched-' . uniqid() . '.example.com',
            'source_url' => 'https://watched.example.com',
            'email' => 'old@watched.example.com',
            'phone' => '+255 700 000 001',
            'address' => '12 Old Street, Dar es Salaam',
            'services' => 'Web design, hosting',
            'rating_avg' => 4.5,
            'rating_count' => 10,
            'last_scraped_at' => now()->subDays(7),
            'scrape_count' => 3,
            'status' => 'new',
        ], $attributes));
    }

    protected function fakeScrape(array $payload): void
    {
        Http::fake([
            '*/api/scraper/scrape' => Http::response(array_merge([
                'success' => true,
                'url' => 'https://watched.example.com',
                'business_name' => 'Watched Cafe',
            ], $payload), 200),
        ]);
    }

    // ------------------------------------------------------------------
    // Success-path detection
    // ------------------------------------------------------------------

    public function test_rating_drop_creates_hot_event_and_alerts_staff(): void
    {
        $business = $this->makeBusiness();

        $this->fakeScrape([
            'url' => $business->website_url,
            'email' => $business->email,
            'rating_avg' => 3.5,
            'rating_count' => 14,
            'reviews' => [],
        ]);

        $result = ScraperClient::make()->scrape($business->website_url);
        $this->assertTrue($result['ok']);

        $event = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_RATING_DROP)
            ->first();

        $this->assertNotNull($event, 'rating_drop event should be recorded');
        $this->assertTrue($event->isHot());
        $this->assertSame(3.5, (float) $event->changes['rating_avg']['to']);
        $this->assertNotNull($event->notified_at, 'hot event should trigger a staff alert');
        $this->assertSame(1, Notification::where('type', 'watchdog_alert')->count());
    }

    public function test_small_rating_change_below_threshold_is_ignored(): void
    {
        $business = $this->makeBusiness(['rating_avg' => 4.5]);

        $this->fakeScrape([
            'url' => $business->website_url,
            'rating_avg' => 4.2, // drop of 0.3 < 0.5 threshold
            'reviews' => [],
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $this->assertSame(0, ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_RATING_DROP)->count());
        $this->assertSame(0, Notification::where('type', 'watchdog_alert')->count());
    }

    public function test_rating_improvement_creates_no_event(): void
    {
        $business = $this->makeBusiness(['rating_avg' => 4.0]);

        $this->fakeScrape([
            'url' => $business->website_url,
            'rating_avg' => 4.8,
            'reviews' => [],
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $this->assertSame(0, ScrapeWatchEvent::where('scraped_business_id', $business->id)->count());
    }

    public function test_new_negative_review_is_hot_and_alerts_staff(): void
    {
        $business = $this->makeBusiness();

        $this->fakeScrape([
            'url' => $business->website_url,
            'rating_avg' => 4.5,
            'reviews' => [
                ['author' => 'Bob', 'text' => 'Terrible experience, never again.', 'rating' => 1.0, 'source' => 'on-page'],
            ],
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $event = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_NEW_REVIEW)
            ->first();

        $this->assertNotNull($event);
        $this->assertTrue($event->isHot());
        $this->assertSame('Bob', $event->changes['author']);
        $this->assertNotNull($event->notified_at);
    }

    public function test_new_positive_review_is_noteworthy_only(): void
    {
        $business = $this->makeBusiness();

        $this->fakeScrape([
            'url' => $business->website_url,
            'reviews' => [
                ['author' => 'Grace', 'text' => 'Absolutely wonderful service.', 'rating' => 5.0, 'source' => 'on-page'],
            ],
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $event = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_NEW_REVIEW)
            ->first();

        $this->assertNotNull($event);
        $this->assertFalse($event->isHot());
        $this->assertNull($event->notified_at, 'non-hot events must not alert staff');
    }

    public function test_repeat_review_is_not_reported_twice(): void
    {
        $business = $this->makeBusiness();
        $review = ['author' => 'Carol', 'text' => 'Good value for money.', 'rating' => 4.0, 'source' => 'on-page'];

        $this->fakeScrape(['url' => $business->website_url, 'reviews' => [$review]]);
        ScraperClient::make()->scrape($business->website_url);
        $this->assertSame(1, ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_NEW_REVIEW)->count());

        // Second scrape with the SAME review must not create another event.
        $this->fakeScrape(['url' => $business->website_url, 'reviews' => [$review]]);
        ScraperClient::make()->scrape($business->website_url);

        $this->assertSame(1, ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_NEW_REVIEW)->count());
    }

    public function test_contact_change_is_recorded_without_alert(): void
    {
        $business = $this->makeBusiness();

        $this->fakeScrape([
            'url' => $business->website_url,
            'email' => 'new@watched.example.com',
            'phone' => $business->phone,
            'reviews' => [],
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $event = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_CONTACT_CHANGED)
            ->first();

        $this->assertNotNull($event);
        $this->assertSame('email', $event->changes['field']);
        $this->assertFalse($event->isHot());
        $this->assertNull($event->notified_at);
    }

    public function test_first_scrape_establishes_baseline_only(): void
    {
        $business = $this->makeBusiness(['last_scraped_at' => null, 'scrape_count' => 0]);

        $this->fakeScrape([
            'url' => $business->website_url,
            'email' => 'first@watched.example.com',
            'rating_avg' => 4.0,
            'reviews' => [
                ['author' => 'Dave', 'text' => 'First impression: solid.', 'rating' => 1.0, 'source' => 'on-page'],
            ],
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $this->assertSame(0, ScrapeWatchEvent::where('scraped_business_id', $business->id)->count(),
            'baseline scrape must not create events');
        $this->assertNotNull($business->fresh()->seen_review_texts, 'baseline must store seen reviews');
    }

    // ------------------------------------------------------------------
    // Availability watching
    // ------------------------------------------------------------------

    public function test_failed_rescrape_records_site_down_and_alerts(): void
    {
        $business = $this->makeBusiness();

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => false,
                'url' => $business->website_url,
                'message' => 'Website returned HTTP 500',
            ], 422),
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $event = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_SITE_DOWN)
            ->first();

        $this->assertNotNull($event);
        $this->assertTrue($event->isHot());
        $this->assertNotNull($event->notified_at);
    }

    public function test_site_down_is_not_recorded_for_never_scraped_business(): void
    {
        $business = $this->makeBusiness(['scrape_count' => 0, 'last_scraped_at' => null]);

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => false,
                'url' => $business->website_url,
                'message' => 'Could not reach website: timeout',
            ], 422),
        ]);

        ScraperClient::make()->scrape($business->website_url);

        $this->assertSame(0, ScrapeWatchEvent::where('scraped_business_id', $business->id)->count());
    }

    public function test_recovery_after_outage_records_back_online(): void
    {
        $business = $this->makeBusiness();

        // Http::fake stubs merge (first match wins), so a failure→success
        // transition needs a sequence, not two fake() calls.
        Http::fake([
            '*/api/scraper/scrape' => Http::sequence()
                ->push(['success' => false, 'url' => $business->website_url, 'message' => 'HTTP 500'], 422)
                ->push(['success' => true, 'url' => $business->website_url, 'business_name' => 'Watched Cafe', 'reviews' => []], 200),
        ]);
        ScraperClient::make()->scrape($business->website_url);
        ScraperClient::make()->scrape($business->website_url);

        $this->assertSame(1, ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_BACK_ONLINE)->count());
    }

    // ------------------------------------------------------------------
    // Cooldown
    // ------------------------------------------------------------------

    public function test_hot_alerts_respect_cooldown_window(): void
    {
        $business = $this->makeBusiness();

        // First hot event: site down → alerts. Second scrape: rating drop
        // inside the cooldown window → event recorded, NO new alert.
        // (Http::fake stubs merge, so a varying response needs a sequence.)
        Http::fake([
            '*/api/scraper/scrape' => Http::sequence()
                ->push(['success' => false, 'url' => $business->website_url, 'message' => 'HTTP 500'], 422)
                ->push(['success' => true, 'url' => $business->website_url, 'business_name' => 'Watched Cafe', 'rating_avg' => 2.0, 'reviews' => []], 200),
        ]);
        ScraperClient::make()->scrape($business->website_url);
        $this->assertSame(1, Notification::where('type', 'watchdog_alert')->count());

        ScraperClient::make()->scrape($business->website_url);

        $drop = ScrapeWatchEvent::where('scraped_business_id', $business->id)
            ->ofType(ScrapeWatchEvent::TYPE_RATING_DROP)->first();
        $this->assertNotNull($drop, 'event is still recorded');
        $this->assertNull($drop->notified_at, 'no alert within cooldown window');
        $this->assertSame(1, Notification::where('type', 'watchdog_alert')->count(), 'alert count unchanged');
    }

    public function test_watchdog_can_be_disabled_by_config(): void
    {
        config(['owers.scraper.watchdog.enabled' => false]);
        $business = $this->makeBusiness();

        $this->fakeScrape(['url' => $business->website_url, 'rating_avg' => 1.0, 'reviews' => []]);
        ScraperClient::make()->scrape($business->website_url);

        $this->assertSame(0, ScrapeWatchEvent::where('scraped_business_id', $business->id)->count());
    }

    public function test_watchdog_panel_renders_on_business_page(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();
        $business = $this->makeBusiness();

        ScrapeWatchEvent::create([
            'scraped_business_id' => $business->id,
            'type' => ScrapeWatchEvent::TYPE_RATING_DROP,
            'severity' => ScrapeWatchEvent::SEVERITY_HOT,
            'changes' => ['rating_avg' => ['from' => 4.5, 'to' => 3.0]],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.scraped-businesses.show', $business));

        $response->assertStatus(200);
        $response->assertSee('Watchdog Activity');
        $response->assertSee('Rating drop');
    }

    public function test_dashboard_shows_watchdog_feed_across_all_businesses(): void
    {
        $admin = \App\Models\User::where('role', 'admin')->firstOrFail();

        $down = $this->makeBusiness(['business_name' => 'Outage Cafe']);
        $review = $this->makeBusiness(['business_name' => 'Reviews Cafe']);

        ScrapeWatchEvent::create([
            'scraped_business_id' => $down->id,
            'type' => ScrapeWatchEvent::TYPE_SITE_DOWN,
            'severity' => ScrapeWatchEvent::SEVERITY_HOT,
            'changes' => ['message' => 'Website could not be reached.'],
            'notified_at' => now(),
        ]);
        ScrapeWatchEvent::create([
            'scraped_business_id' => $review->id,
            'type' => ScrapeWatchEvent::TYPE_NEW_REVIEW,
            'severity' => ScrapeWatchEvent::SEVERITY_NOTEWORTHY,
            'changes' => ['author' => 'Zawadi', 'rating' => 5.0, 'text' => 'Superb service.'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Watchdog Activity');
        $response->assertSee('Outage Cafe');
        $response->assertSee('Reviews Cafe');
        $response->assertSee('Site down');
        $response->assertSee('New review');
        $response->assertSee('1 hot this week');
        // Full event text stays out of the feed; it lives on the business page.
        $response->assertSee(route('admin.scraped-businesses.show', $down), false);
    }
}
