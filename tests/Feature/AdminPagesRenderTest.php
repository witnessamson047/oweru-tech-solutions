<?php

namespace Tests\Feature;

use App\Models\CarePlan;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\PackageExclusion;
use App\Models\Recommendation;
use App\Models\Scan;
use App\Models\ScrapedBusiness;
use App\Models\ScannerCheck;
use App\Models\ServicePackage;
use App\Models\User;
use App\Models\Website;
use Tests\TestCase;

/**
 * Every admin GET page must return 200 for a logged-in staff member.
 *
 * This exists because three pages shipped broken for weeks and nobody noticed:
 * admin.packages.show pointed at a method that did not exist, and
 * admin.recommendations.create / .edit pointed at views that were never
 * written — all three were live links in the sidebar or on an index page.
 *
 * Blade compiles without executing, so view:cache and a passing unit suite
 * will not catch a missing view. Hitting the URL does.
 */
class AdminPagesRenderTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
    }

    public static function adminPages(): array
    {
        return [
            'dashboard' => ['admin.dashboard'],
            'pipeline' => ['admin.pipeline.index'],
            'enquiries' => ['admin.enquiries.index'],
            'websites' => ['admin.websites.index'],
            'websites create' => ['admin.websites.create'],
            'scans' => ['admin.scans.index'],
            'reports' => ['admin.reports.index'],
            'recommendations' => ['admin.recommendations.index'],
            'recommendations create' => ['admin.recommendations.create'],
            'scanner checks' => ['admin.scanner-checks.index'],
            'scanner checks create' => ['admin.scanner-checks.create'],
            'packages' => ['admin.packages.index'],
            'packages create' => ['admin.packages.create'],
            'care plans' => ['admin.care-plans.index'],
            'care plans create' => ['admin.care-plans.create'],
            'package exclusions' => ['admin.package-exclusions.index'],
            'package exclusions create' => ['admin.package-exclusions.create'],
            'invoices' => ['admin.invoices.index'],
            'invoices create' => ['admin.invoices.create'],
            'payments' => ['admin.payments.index'],
            'scraped businesses' => ['admin.scraped-businesses.index'],
            'scrape targets' => ['admin.scrape-targets.index'],
            'discovery' => ['admin.discovery.index'],
            'discovery leads' => ['admin.discovery.leads'],
            'account password' => ['admin.account.password'],
        ];
    }

    /**
     * @dataProvider adminPages
     */
    public function test_admin_page_renders(string $routeName): void
    {
        $response = $this->get(route($routeName));
        $response->assertStatus(200);

        // Every admin page must carry a reachable logout (sidebar footer).
        $response->assertSee('action="'.route('logout').'"', false);
    }

    /**
     * Detail pages take a route parameter, so they cannot live in adminPages.
     * They are the pages most likely to rot: editing an index never touches
     * them.
     *
     * These fixtures are built by hand because the seeder only ships catalogue
     * data (care plans, packages, checks) — not websites, scans, enquiries,
     * invoices or scraped businesses.
     */
    public function test_detail_pages_render(): void
    {
        $enquiry = Enquiry::create([
            'name' => 'Detail Test',
            'business_name' => 'Detail Test Ltd',
            'email' => 'detail@example.test',
            'phone' => '0700000000',
            'country' => 'Tanzania',
            'problem_description' => 'A problem worth describing.',
            'stage' => 'new',
            'source' => 'direct',
        ]);

        $website = Website::create([
            'business_name' => 'Detail Test Ltd',
            'url' => 'https://detail.example.test',
            'sector' => 'Retail',
            'status' => 'active',
        ]);

        $scan = Scan::create([
            'website_id' => $website->id,
            'url' => $website->url,
            'status' => 'completed',
            'score' => 72,
            'band' => 'Adequate',
            'started_at' => now(),
        ]);

        $invoice = Invoice::create([
            'number' => 'INV-DETAIL-1',
            'enquiry_id' => $enquiry->id,
            'title' => 'Detail test invoice',
            'total' => 100000,
            'currency' => 'TZS',
            'deposit_percent' => 50,
            'deposit_due' => 50000,
            'status' => Invoice::STATUS_DRAFT,
        ]);

        $business = ScrapedBusiness::create([
            'business_name' => 'Scraped Detail',
            'website_url' => 'https://scraped.example.test',
            'source_url' => 'https://scraped.example.test',
            'status' => 'new',
        ]);

        $this->get(route('admin.websites.show', $website))->assertStatus(200);
        $this->get(route('admin.scans.show', $scan))->assertStatus(200);
        $this->get(route('admin.enquiries.show', $enquiry))->assertStatus(200);
        $this->get(route('admin.invoices.show', $invoice))->assertStatus(200);
        $this->get(route('admin.scraped-businesses.show', $business))->assertStatus(200);
    }

    /**
     * Every admin link in the sidebar must point at a route that exists, so a
     * nav typo cannot ship a 404 to staff.
     */
    public function test_every_sidebar_nav_route_is_registered(): void
    {
        $navRoutes = [
            'admin.dashboard', 'admin.pipeline.index', 'admin.enquiries.index',
            'admin.discovery.index', 'admin.discovery.leads', 'admin.scrape-targets.index',
            'admin.scraped-businesses.index', 'admin.websites.index', 'admin.scans.index',
            'admin.reports.index', 'admin.packages.index', 'admin.care-plans.index',
            'admin.package-exclusions.index', 'admin.recommendations.index',
            'admin.scanner-checks.index', 'admin.invoices.index', 'admin.payments.index',
            'admin.account.password', 'home', 'logout',
        ];

        foreach ($navRoutes as $name) {
            $this->assertNotNull(
                app('router')->getRoutes()->getByName($name),
                "Sidebar links to [{$name}] but no such route is registered."
            );
        }
    }

    /**
     * Lists that grow without bound must paginate, or the panel gets slower
     * every week. This locks in the contract for every admin table: the view
     * must receive an AbstractPaginator, never a bare ->get() collection.
     *
     * @dataProvider paginatedLists
     */
    public function test_list_pages_paginate(string $routeName): void
    {
        $response = $this->get(route($routeName));
        $response->assertStatus(200);

        $data = $response->baseResponse->original->getData();

        $paginators = array_filter(
            $data,
            fn ($value) => $value instanceof \Illuminate\Contracts\Pagination\Paginator
        );

        $this->assertNotEmpty(
            $paginators,
            "[{$routeName}] rendered without a paginator — use ->paginate() so this list cannot grow unbounded."
        );
    }

    /**
     * @dataProvider paginatedLists
     */
    public function test_list_pages_keep_filters_when_paging(string $routeName): void
    {
        // Request page 2 with a filter present. The pagination links must carry
        // the filter, otherwise staff silently lose their filter on click.
        $response = $this->get(route($routeName, ['page' => 2, 'search' => 'zzz-no-such-thing']));
        $response->assertStatus(200);
    }

    /**
     * withQueryString() is easy to forget, and the symptom is nasty: a staff
     * member filters to "Active", clicks page 2, and gets an unfiltered list
     * with no obvious reason why.
     *
     * Seed data is far too small to paginate, so seed enough rows to force a
     * real second page, then assert the rendered pager links keep the filter.
     */
    public function test_pagination_links_preserve_query_string(): void
    {
        // Seed data is far too small to paginate, so force a real second page.
        // The base TestCase uses RefreshDatabase, so these rows roll back.
        $plans = collect(range(1, 30))->map(fn (int $i) => CarePlan::create([
            'name' => "Paging Fixture {$i}",
            'slug' => "paging-fixture-{$i}",
            'description' => 'Created by the pagination regression test.',
            'price_tzs' => 1000 * $i,
            'price_usd' => 10 * $i,
            'is_featured' => false,
            'active' => true,
        ]));

        $this->assertCount(30, $plans);

        // Both filters must still match rows, otherwise there is only one page
        // and there is no pager to inspect.
        $html = $this->get(route('admin.care-plans.index', [
            'status' => 'active',
            'search' => 'Paging Fixture',
        ]))->assertStatus(200)->getContent();

        preg_match_all('/href="([^"]*)"/', $html, $all);

        $pageTwo = array_values(array_filter(
            $all[1],
            fn ($href) => str_contains(html_entity_decode($href, ENT_QUOTES), 'page=2')
        ));

        $this->assertNotEmpty($pageTwo, 'Expected a link to page 2 once enough rows match.');

        foreach ($pageTwo as $href) {
            $decoded = html_entity_decode($href, ENT_QUOTES);
            $this->assertStringContainsString('status=active', $decoded,
                "Pagination link dropped the status filter: {$decoded}");
            $this->assertStringContainsString('search=Paging', $decoded,
                "Pagination link dropped the search filter: {$decoded}");
        }
    }

    /**
     * HTML does not allow a <form> inside a <form>. Browsers silently drop the
     * inner one, so a nested delete button either does nothing or submits the
     * outer form's method. Catch it here rather than in a support ticket.
     *
     * Note: sibling forms are perfectly valid (a search form next to a save
     * form), so this counts *depth*, not total forms.
     */
    public function test_pages_do_not_nest_forms(): void
    {
        foreach (self::formPages() as [$label, $routeName, $modelClass]) {
            $param = $modelClass ? $modelClass::query()->first() : null;

            if ($modelClass) {
                $this->assertNotNull($param, "No seeded {$modelClass} row to open [{$routeName}].");
            }

            $url = $param ? route($routeName, $param) : route($routeName);

            $html = $this->get($url)->assertStatus(200)->getContent();

            $depth = $this->maxFormDepth($html);

            $this->assertLessThanOrEqual(
                1,
                $depth,
                "[{$label} / {$routeName}] nests forms {$depth} deep — browsers drop the inner form."
            );
        }
    }

    /**
     * Walk the HTML and return the deepest <form> nesting level.
     */
    private function maxFormDepth(string $html): int
    {
        $depth = 0;
        $max = 0;

        if (preg_match_all('/<(\/?)form\b[^>]*>/i', $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $depth += $match[1] === '/' ? -1 : 1;
                $max = max($max, $depth);
            }
        }

        return max($max, 0);
    }

    /**
     * Form pages as [label, routeName, modelClass|null]. Classes only — see
     * detailPages() for why the database is never touched here.
     *
     * @return array<int, array{0: string, 1: string, 2: class-string|null}>
     */
    public static function formPages(): array
    {
        return [
            ['care plans create', 'admin.care-plans.create', null],
            ['care plans edit', 'admin.care-plans.edit', CarePlan::class],
            ['packages create', 'admin.packages.create', null],
            ['packages edit', 'admin.packages.edit', ServicePackage::class],
            ['recommendations create', 'admin.recommendations.create', null],
            ['recommendations edit', 'admin.recommendations.edit', Recommendation::class],
            ['exclusions create', 'admin.package-exclusions.create', null],
            ['exclusions edit', 'admin.package-exclusions.edit', PackageExclusion::class],
            ['scanner checks create', 'admin.scanner-checks.create', null],
            ['scanner checks edit', 'admin.scanner-checks.edit', ScannerCheck::class],
            ['invoices create', 'admin.invoices.create', null],
            ['websites create', 'admin.websites.create', null],
            ['scrape targets', 'admin.scrape-targets.index', null],
        ];
    }

    public static function paginatedLists(): array
    {
        return [
            ['admin.recommendations.index'],
            ['admin.package-exclusions.index'],
            ['admin.enquiries.index'],
            ['admin.websites.index'],
            ['admin.scans.index'],
            ['admin.invoices.index'],
            ['admin.payments.index'],
            ['admin.reports.index'],
            ['admin.scraped-businesses.index'],
            ['admin.scrape-targets.index'],
            ['admin.discovery.index'],
            ['admin.discovery.leads'],
            ['admin.scanner-checks.index'],
            ['admin.packages.index'],
            ['admin.care-plans.index'],
        ];
    }
}