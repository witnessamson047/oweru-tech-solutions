<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DashboardScannerHealthTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
        Cache::forget('scanner.health');
    }

    public function test_dashboard_shows_online_badge_when_scanner_reachable(): void
    {
        Http::fake(['*/health' => Http::response(['status' => 'healthy'], 200)]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Scanner engine online');
        $response->assertDontSee('Scanner engine offline');
    }

    public function test_dashboard_shows_offline_badge_when_scanner_unreachable(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('refused'));

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Scanner engine offline');
        $response->assertDontSee('Scanner engine online');
    }

    public function test_offline_dashboard_shows_scanner_log_tail(): void
    {
        Http::fake(fn () => throw new \Illuminate\Http\Client\ConnectionException('refused'));

        $response = $this->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('scanner-service.log');
    }
}
