<?php

namespace App\Providers;

use App\Models\DiscoveryLead;
use App\Models\Enquiry;
use App\Models\Invoice;
use App\Models\ScrapeTarget;
use App\Observers\EnquiryObserver;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Enquiry::observe(EnquiryObserver::class);

        $this->shareAdminNavCounts();
    }

    /**
     * Give the admin sidebar live counters so staff can see where work is
     * waiting without visiting every page.
     *
     * Three cheap COUNTs, cached for 60s (counts change on the order of
     * minutes, and the sidebar renders on every single admin page — an
     * uncached version would add 3 queries to every request).
     */
    private function shareAdminNavCounts(): void
    {
        View::composer('layouts.admin', function ($view) {
            try {
                $navCounts = Cache::remember('admin.nav_counts', 60, function () {
                    return [
                        // Leads nobody has worked yet — the number that matters most.
                        'enquiries_new' => Enquiry::where('stage', 'new')->count(),
                        // Overdue deposit reminders waiting on a phone call.
                        'invoices_overdue' => Invoice::query()
                            ->whereIn('status', [Invoice::STATUS_ISSUED, Invoice::STATUS_PART_PAID])
                            ->whereNotNull('due_at')
                            ->where('due_at', '<', now())
                            ->count(),
                        // Outreach list not yet worked.
                        'leads_new' => DiscoveryLead::where('status', DiscoveryLead::STATUS_NEW)->count(),
                        // Targets waiting in the auto-scraper queue.
                        'targets_active' => ScrapeTarget::where('status', ScrapeTarget::STATUS_ACTIVE)->count(),
                    ];
                });
            } catch (\Throwable) {
                // The sidebar renders on the graceful error page too — when the
                // data layer is down, counters are cosmetic, so show zeros
                // instead of crashing the fallback page itself.
                $navCounts = [
                    'enquiries_new' => 0,
                    'invoices_overdue' => 0,
                    'leads_new' => 0,
                    'targets_active' => 0,
                ];
            }

            $view->with('navCounts', $navCounts);
        });
    }
}