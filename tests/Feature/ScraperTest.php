<?php

namespace Tests\Feature;

use App\Models\Enquiry;
use App\Models\Scan;
use App\Models\ScrapedBusiness;
use App\Models\ScrapeTarget;
use App\Models\User;
use App\Models\Website;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ScraperTest extends TestCase
{
    private User $admin;

    private array $createdBusinessIds = [];

    private array $createdEnquiryIds = [];

    private array $createdWebsiteIds = [];

    private array $discoverySourceUrls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        ScrapedBusiness::whereIn('id', $this->createdBusinessIds)->delete();
        Enquiry::whereIn('id', $this->createdEnquiryIds)->delete();
        Website::whereIn('id', $this->createdWebsiteIds)->delete();

        foreach ($this->discoverySourceUrls as $sourceUrl) {
            ScrapeTarget::where('notes', "auto-discovered from {$sourceUrl}")->delete();
        }

        parent::tearDown();
    }

    private function makeBusiness(array $overrides = []): ScrapedBusiness
    {
        $suffix = uniqid();

        $business = ScrapedBusiness::create(array_merge([
            'business_name' => 'Test Biz ' . $suffix,
            'website_url' => "https://test-{$suffix}.example.com",
            'email' => "info@test-{$suffix}.example.com",
            'source_url' => "https://test-{$suffix}.example.com",
            'status' => 'new',
        ], $overrides));

        $this->createdBusinessIds[] = $business->id;

        return $business;
    }

    public function test_index_page_renders_for_admin(): void
    {
        $response = $this->get(route('admin.scraped-businesses.index'));

        $response->assertStatus(200);
        $response->assertSee('Oweru Scraper');
        $response->assertSee('Scrape a public website');
    }

    public function test_successful_scrape_is_stored(): void
    {
        $url = 'https://scrape-ok-' . uniqid() . '.example.com';

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => true,
                'url' => $url,
                'business_name' => 'ABC Company',
                'email' => 'info@abc.co.tz',
                'phone' => '+255711890764',
                'address' => 'P.O. Box 1234, Dar es Salaam',
                'services' => 'Web design, hosting and domain registration',
                'about' => 'ABC Company is a Dar es Salaam based technology firm.',
            ], 200),
        ]);

        $response = $this->post(route('admin.scraped-businesses.store'), [
            'url' => $url,
        ]);

        $response->assertRedirect();

        $business = ScrapedBusiness::where('website_url', $url)->first();
        $this->createdBusinessIds[] = $business->id;

        $this->assertNotNull($business);
        $this->assertSame('ABC Company', $business->business_name);
        $this->assertSame('info@abc.co.tz', $business->email);
        $this->assertSame('new', $business->status);
    }

    public function test_preliminary_gaps_are_stored_from_scrape_response(): void
    {
        $url = 'https://gaps-ok-' . uniqid() . '.example.com';

        Http::fake([
            '*/api/scraper/scrape' => Http::response(array_merge([
                'success' => true,
                'url' => $url,
                'business_name' => 'Gap Co',
                'email' => 'info@gap.example.com',
            ], [
                'preliminary_gaps' => [
                    ['check_name' => 'SSL Certificate Valid', 'gap' => 'No HTTPS — the site does not use a secure connection', 'opportunity' => 'Security upgrade + trust building (SSL, security headers)'],
                    ['check_name' => 'Responsive Layout', 'gap' => 'Not mobile-friendly — no responsive viewport meta tag', 'opportunity' => 'Mobile-responsive redesign'],
                    ['check_name' => 'Online Payment/Booking Path', 'gap' => 'No online payment or booking path', 'opportunity' => 'E-commerce / booking integration'],
                ],
            ]), 200),
        ]);

        $response = $this->post(route('admin.scraped-businesses.store'), ['url' => $url]);
        $response->assertRedirect();

        $business = ScrapedBusiness::where('website_url', $url)->first();
        $this->createdBusinessIds[] = $business->id;

        $this->assertNotNull($business);
        $this->assertSame(3, $business->preliminaryGapCount());
        $this->assertSame('high', $business->outreachPriority());
        $this->assertSame(
            'Online Payment/Booking Path',
            $business->preliminary_gaps[2]['check_name'],
            'check_name must match scanner checks so the Recommendation mapping attaches'
        );
    }

    public function test_show_page_lists_preliminary_gaps_and_priority(): void
    {
        $business = $this->makeBusiness([
            'preliminary_gaps' => [
                ['check_name' => 'SSL Certificate Valid', 'gap' => 'No HTTPS', 'opportunity' => 'Security upgrade'],
                ['check_name' => 'Responsive Layout', 'gap' => 'No viewport meta tag', 'opportunity' => 'Mobile redesign'],
            ],
        ]);

        $response = $this->get(route('admin.scraped-businesses.show', $business));

        $response->assertStatus(200);
        $response->assertSee('Preliminary Gaps', false);
        $response->assertSee('No HTTPS');
        // The gap must now be paired with the mapped Oweru service from the
        // recommendations table (SSL Certificate Valid -> SSL Setup & Configuration)
        $response->assertSee('SSL Setup & Configuration');
        $response->assertSee('medium priority');
    }

    public function test_index_shows_gap_count_badge(): void
    {
        $this->makeBusiness([
            'preliminary_gaps' => [
                ['check_name' => 'SSL Certificate Valid', 'gap' => 'No HTTPS', 'opportunity' => 'Security upgrade'],
                ['check_name' => 'Responsive Layout', 'gap' => 'No viewport', 'opportunity' => 'Mobile redesign'],
                ['check_name' => 'Online Payment/Booking Path', 'gap' => 'No payment path', 'opportunity' => 'E-commerce'],
                ['check_name' => 'Current Copyright Year', 'gap' => 'Copyright 2022', 'opportunity' => 'Maintenance plan'],
            ],
        ]);

        $response = $this->get(route('admin.scraped-businesses.index'));

        $response->assertStatus(200);
        $response->assertSee('4 gaps');
        $response->assertSee('badge-danger', false);
    }

    public function test_index_shows_mapped_oweru_services_for_gaps(): void
    {
        $this->makeBusiness([
            'preliminary_gaps' => [
                ['check_name' => 'SSL Certificate Valid', 'gap' => 'No HTTPS', 'opportunity' => 'Security upgrade'],
                ['check_name' => 'Responsive Layout', 'gap' => 'No viewport', 'opportunity' => 'Mobile redesign'],
            ],
        ]);

        $response = $this->get(route('admin.scraped-businesses.index'));

        $response->assertStatus(200);
        // Mapped services from the recommendations table appear on the list
        $response->assertSee('Website Maintenance');   // SSL Certificate Valid
        $response->assertSee('Responsive Web Development'); // Responsive Layout
    }

    public function test_export_downloads_csv_with_mapped_services(): void
    {
        $business = $this->makeBusiness([
            'preliminary_gaps' => [
                ['check_name' => 'SSL Certificate Valid', 'gap' => 'No HTTPS', 'opportunity' => 'Security upgrade'],
                ['check_name' => 'Meta Description Present', 'gap' => 'No meta description', 'opportunity' => 'SEO'],
            ],
            'email' => 'safe@example.com',
        ]);

        $response = $this->get(route('admin.scraped-businesses.export', ['status' => 'new']));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));

        $body = $response->streamedContent();
        $this->assertStringContainsString('Business Name', $body, 'header row present');
        $this->assertStringContainsString($business->business_name, $body);
        $this->assertStringContainsString('Scraped From', $body);
        $this->assertStringContainsString('Website Maintenance', $body, 'mapped service (SSL) in CSV');
        $this->assertStringContainsString('Website Improvement', $body, 'mapped service (meta) in CSV');
    }
    public function test_failed_scrape_shows_error_without_creating_record(): void
    {
        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => false,
                'message' => 'Website disallows crawling via robots.txt',
            ], 422),
        ]);

        $response = $this->from(route('admin.scraped-businesses.index'))
            ->post(route('admin.scraped-businesses.store'), [
                'url' => 'https://robots-blocked-' . uniqid() . '.example.com',
            ]);

        $response->assertRedirect(route('admin.scraped-businesses.index'));
        $response->assertSessionHas('error');
    }

    public function test_scrape_requires_valid_url(): void
    {
        $response = $this->post(route('admin.scraped-businesses.store'), [
            'url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('url');
    }

    public function test_run_health_scan_creates_website_and_scan(): void
    {
        $business = $this->makeBusiness();

        Http::fake([
            '*/api/scanner/scan' => Http::response([
                'success' => true,
                'score' => 64,
                'band' => 'Adequate',
                'results' => [
                    [
                        'check_name' => 'SSL Certificate Valid',
                        'area' => 'Security',
                        'weight' => 10,
                        'passed' => true,
                        'points' => 10,
                        'evidence' => 'HTTPS valid',
                        'finding_text' => 'Valid certificate',
                    ],
                ],
            ], 200),
        ]);

        $response = $this->post(route('admin.scraped-businesses.run-health-scan', $business));

        $response->assertRedirect();

        $website = Website::where('url', $business->website_url)->first();
        $this->createdWebsiteIds[] = $website->id;

        $this->assertNotNull($website);
        $this->assertSame('scanned', $business->fresh()->status);
        $this->assertSame($website->id, $business->fresh()->website_id);

        $scan = Scan::where('website_id', $website->id)->first();
        $this->assertNotNull($scan);
        $this->assertSame(64, $scan->score);
    }

    public function test_add_to_leads_creates_outreach_enquiry(): void
    {
        $business = $this->makeBusiness([
            'business_name' => 'XYZ Hotel',
            'about' => 'A boutique hotel in Arusha.',
        ]);

        $response = $this->post(route('admin.scraped-businesses.add-to-leads', $business), [
            'name' => 'Jane Contact',
        ]);

        $response->assertRedirect();

        $enquiry = Enquiry::where('email', $business->email)->first();
        $this->createdEnquiryIds[] = $enquiry->id;

        $this->assertNotNull($enquiry);
        $this->assertSame('outreach', $enquiry->source);
        $this->assertSame('new', $enquiry->stage);
        $this->assertSame('lead', $business->fresh()->status);
        $this->assertSame($enquiry->id, $business->fresh()->enquiry_id);
    }

    public function test_add_to_leads_does_not_duplicate_existing_enquiry(): void
    {
        $enquiry = Enquiry::create([
            'name' => 'Existing Contact',
            'business_name' => 'Dup Ltd',
            'email' => 'dup-' . uniqid() . '@example.com',
            'phone' => '+255700000000',
            'problem_description' => 'Existing pipeline enquiry.',
            'budget_range' => 'Prefer not to say',
            'stage' => 'new',
            'source' => 'outreach',
        ]);
        $this->createdEnquiryIds[] = $enquiry->id;

        $business = $this->makeBusiness([
            'status' => 'lead',
            'enquiry_id' => $enquiry->id,
        ]);

        $response = $this->post(route('admin.scraped-businesses.add-to-leads', $business));

        $response->assertRedirect(route('admin.enquiries.show', $enquiry));

        $this->assertSame(1, Enquiry::where('email', $enquiry->email)->count());
    }

    public function test_directory_scrape_auto_queues_discovered_targets(): void
    {
        $url = 'https://directory-' . uniqid() . '.example.com';
        $this->discoverySourceUrls[] = $url;

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => true,
                'url' => $url,
                'business_name' => 'TZ Business Directory',
                'discovered_links' => [
                    'https://kaa-guesthouse.co.tz/listing/12',
                    'http://mwangaza-solar.co.tz',
                    'https://www.nyumba-realestate.co.tz/listings',
                    'https://chapati-express.or.tz/menu',
                    'https://simba-porters.com/',
                    'https://kaa-guesthouse.co.tz/listing/33', // same host as first → deduped
                    'not a url',                                // invalid → dropped
                    'https://www.facebook.com/kaa-guesthouse',  // junk → dropped
                ],
            ], 200),
        ]);

        $response = $this->post(route('admin.scraped-businesses.store'), [
            'url' => $url,
        ]);

        $response->assertRedirect();

        $targets = ScrapeTarget::where('notes', "auto-discovered from {$url}")->get();

        // 5 valid distinct hosts survive; junk filtered; duplicate host deduped
        $this->assertSame(5, $targets->count());
        $this->assertSame(ScrapeTarget::STATUS_ACTIVE, $targets->first()->status);
        $this->assertNotNull(ScrapeTarget::where('url', 'https://kaa-guesthouse.co.tz/listing/12')->first());
        $this->assertNull(ScrapeTarget::where('url', 'https://www.facebook.com/kaa-guesthouse')->first());

        // Re-scraping the same directory must not duplicate targets
        $this->post(route('admin.scraped-businesses.store'), ['url' => $url]);
        $this->assertSame(5, ScrapeTarget::where('notes', "auto-discovered from {$url}")->count());
    }

    public function test_discovered_targets_are_capped_per_directory(): void
    {
        // Cap mechanics test: raise the limit explicitly (production default
        // is 5, a drip that keeps profile-based discovery fast).
        config(['owers.scraper.discovery_limit' => 20]);

        $url = 'https://bigdir-' . uniqid() . '.example.com';
        $this->discoverySourceUrls[] = $url;

        $links = [];
        for ($i = 1; $i <= 30; $i++) {
            $links[] = "https://biz-{$i}-" . uniqid() . '.co.tz';
        }

        Http::fake([
            '*/api/scraper/scrape' => Http::response([
                'success' => true,
                'url' => $url,
                'business_name' => 'Big Directory',
                'discovered_links' => $links,
            ], 200),
        ]);

        $response = $this->post(route('admin.scraped-businesses.store'), [
            'url' => $url,
        ]);

        $response->assertRedirect();

        // Default discovery_limit = 20
        $this->assertSame(
            20,
            ScrapeTarget::where('notes', "auto-discovered from {$url}")->count(),
        );
    }
}
