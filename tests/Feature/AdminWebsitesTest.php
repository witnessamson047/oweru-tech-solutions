<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Website;
use Tests\TestCase;

class AdminWebsitesTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
    }

    public function test_create_page_returns_200(): void
    {
        $response = $this->get(route('admin.websites.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Website');
        $response->assertSee('business_name');
    }

    public function test_store_creates_website(): void
    {
        $url = 'https://test-store-' . uniqid() . '.example.com';

        $response = $this->post(route('admin.websites.store'), [
            'business_name' => 'Test Store Co',
            'url' => $url,
            'sector' => 'Retail',
        ]);

        $response->assertRedirect(route('admin.websites.index'));
        // Stored canonical form carries a trailing slash on bare roots
        $this->assertDatabaseHas('websites', ['url' => $url . '/', 'business_name' => 'Test Store Co']);

        Website::where('url', $url . '/')->delete();
    }

    public function test_store_validates_url(): void
    {
        $response = $this->post(route('admin.websites.store'), [
            'business_name' => 'Bad Url Co',
            'url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors(['url']);
    }

    public function test_edit_page_returns_200(): void
    {
        // Create our own record instead of assuming seeded data exists
        $website = Website::create([
            'business_name' => 'Edit Fixture Co',
            'url' => 'https://edit-fixture-' . uniqid() . '.example.com',
            'sector' => 'Retail',
        ]);

        $response = $this->get(route('admin.websites.edit', $website));

        $response->assertStatus(200);
        $response->assertSee('Edit Website');
        $response->assertSee($website->business_name);

        $website->delete();
    }

    public function test_update_changes_website(): void
    {
        $website = Website::create([
            'business_name' => 'Update Fixture Co',
            'url' => 'https://update-fixture-' . uniqid() . '.example.com',
            'sector' => 'Retail',
        ]);

        $response = $this->put(route('admin.websites.update', $website), [
            'business_name' => $website->business_name,
            'url' => $website->url,
            'sector' => 'Testing Sector',
        ]);

        $response->assertRedirect(route('admin.websites.show', $website));
        $this->assertDatabaseHas('websites', ['id' => $website->id, 'sector' => 'Testing Sector']);

        $website->delete();
    }
}
