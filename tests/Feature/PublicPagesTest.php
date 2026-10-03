<?php

namespace Tests\Feature;

use App\Models\ServiceLine;
use App\Models\ServicePackage;
use App\Models\User;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    /**
     * Test the home page loads successfully.
     */
    public function test_home_page_returns_200(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /**
     * Test the home page contains expected content.
     */
    public function test_home_page_has_title(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Oweru Tech Solutions');
    }

    public function test_admin_login_page_translates_the_form_and_hides_demo_credentials(): void
    {
        $englishResponse = $this->withSession(['locale' => 'en'])->get('/login');

        $englishResponse->assertOk();
        $englishResponse->assertSee('All your work.');
        $englishResponse->assertSee('name="email"', false);
        $englishResponse->assertSee('name="password"', false);
        $englishResponse->assertSee('Sign in');
        $englishResponse->assertDontSee('Kazi zako zote.');
        $englishResponse->assertDontSee('admin123');

        $swahiliResponse = $this->withSession(['locale' => 'sw'])->get('/login');

        $swahiliResponse->assertOk();
        $swahiliResponse->assertSee('Kazi zako zote.');
        $swahiliResponse->assertSee('Ingia');
        $swahiliResponse->assertDontSee('All your work.');
    }

    public function test_home_page_shows_three_services_and_links_to_all_services(): void
    {
        foreach (range(1, 4) as $index) {
            ServiceLine::create([
                'name' => 'Beyond homepage ' . $index,
                'slug' => 'beyond-homepage-' . $index,
                'icon' => 'layout',
                'description' => 'Overflow test service ' . $index,
                'active' => true,
                'sort_order' => 100 + $index,
            ]);
        }

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertDontSee('Beyond homepage 1');
        $response->assertSee('View all services');
        $response->assertSee('Custom business systems');
        $response->assertSee('images/home-services/software-development.jpg');
        $this->assertSame(3, substr_count($response->getContent(), 'class="home-service-card"'));
    }

    /**
     * Test the enquiry page loads successfully.
     */
    public function test_enquiry_page_returns_200(): void
    {
        $response = $this->get('/enquiry');
        $response->assertStatus(200);
    }

    /**
     * Test the scanner page loads successfully.
     */
    public function test_scanner_page_returns_200(): void
    {
        $response = $this->get('/website-check');
        $response->assertStatus(200);
    }

    /**
     * Test the scanner page contains the scanner form.
     */
    public function test_scanner_page_has_form(): void
    {
        $response = $this->get('/website-check');
        $response->assertStatus(200);
        $response->assertSee('scanner-form');
        $response->assertSee('Scan Website');
    }

    /**
     * Test the enquiry page has the enquiry form.
     */
    public function test_enquiry_page_has_form(): void
    {
        $response = $this->get('/enquiry');
        $response->assertStatus(200);
        $response->assertSee('enquiry');
    }

    public function test_faq_page_returns_200(): void
    {
        // Regression: the FAQ template closed its @php block with @endsection,
        // which is a fatal Blade parse error and a hard 500 for visitors.
        $this->get('/faq')->assertOk();
    }

    /**
     * The debug endpoints leak DB host, mailer and session config. They must
     * never answer an anonymous request, not even in local/testing.
     */
    public function test_debug_routes_reject_anonymous_visitors(): void
    {
        // `auth` redirects guests to the login page rather than 401-ing.
        $this->get('/debug/db')->assertRedirect();
        $this->get('/debug/session')->assertRedirect();
    }

    public function test_debug_routes_reject_authenticated_non_admins(): void
    {
        $user = User::create([
            'name' => 'Regular Client',
            'email' => 'client@example.test',
            'password' => 'password',
            'role' => 'user',
        ]);

        // The admin middleware bounces non-admins to login; the point is that
        // neither endpoint answers with the infrastructure payload.
        $this->actingAs($user)->get('/debug/db')->assertRedirect();
        $this->actingAs($user)->get('/debug/session')->assertRedirect();
    }

    public function test_service_page_shows_six_cards_and_view_more_button(): void
    {
        $serviceLine = ServiceLine::create([
            'name' => 'Web Design',
            'slug' => 'web-design',
            'icon' => 'layout',
            'description' => 'Design services',
            'active' => true,
            'sort_order' => 1,
        ]);

        foreach (range(1, 8) as $index) {
            ServicePackage::create([
                'name' => 'Package ' . $index,
                'slug' => 'package-' . $index,
                'group' => 'individuals',
                'description' => 'Test package ' . $index,
                'service_line_id' => $serviceLine->id,
                'price_tzs' => 100000 * $index,
                'price_usd' => 50 * $index,
                'delivery_days' => 7,
                'is_featured' => false,
                'active' => true,
                'sort_order' => $index,
            ]);
        }

        $response = $this->get('/services');

        $response->assertStatus(200);
        $response->assertSee('View more');
        $this->assertSame(6, substr_count($response->getContent(), 'data-package-card="true"'));
    }
}
