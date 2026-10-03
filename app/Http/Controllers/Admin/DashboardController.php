<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DiscoveryLead;
use App\Models\DiscoveryRun;
use App\Models\Enquiry;
use App\Models\Scan;
use App\Models\ScrapeTarget;
use App\Models\ScrapeWatchEvent;
use App\Models\Website;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
class DashboardController extends Controller
{
    public function index(Request $request)
    {
        try {
            $showAllStats = $request->boolean('show_all_stats', true);

            $stats = [
                'total_enquiries' => Enquiry::count(),
                'new_enquiries' => Enquiry::where('stage', 'new')
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->count(),
                'total_scans' => Scan::where('status', 'completed')->count(),
                'websites' => Website::count(),
                'scored_websites' => Website::whereHas('latestScan', fn($q) => $q->whereNotNull('score'))->count(),
                'priority_prospects' => self::countScannedWebsitesBelow(40),
                'prospects' => self::countScannedWebsitesBelow(60) - self::countScannedWebsitesBelow(40),
                'excluded_websites' => Website::where('exclusion_status', 'excluded')->count(),

                // Discovery funnel: websites + no-website leads found by the
                // OSM discovery runs this week / in total.
                'discovered_week' => ScrapeTarget::whereNotNull('discovery_run_id')
                    ->where('created_at', '>=', now()->subDays(7))->count(),
                'no_website_leads' => DiscoveryLead::new()->count(),
                'discovery_leads_contacted' => DiscoveryLead::where('status', DiscoveryLead::STATUS_CONTACTED)->count(),
            ];

            $recentEnquiries = Enquiry::with('package', 'owner')
                ->latest()
                ->take(5)
                ->get();

            $recentScans = Scan::with('website')
                ->where('status', 'completed')
                ->latest()
                ->take(5)
                ->get();

            $pipelineStats = [];
            foreach (Enquiry::STAGES as $stage) {
                $pipelineStats[$stage] = Enquiry::where('stage', $stage)->count();
            }

            // Website breakdown by sector. Group in SQL — pulling every website
            // row into memory just to count it is what made this page slow.
            $websitesBySector = Website::active()
                ->selectRaw('sector, COUNT(*) as aggregate')
                ->groupBy('sector')
                ->pluck('aggregate', 'sector');

            // Scans by band breakdown — same reasoning.
            $scansByBand = Scan::where('status', 'completed')
                ->selectRaw('band, COUNT(*) as aggregate')
                ->groupBy('band')
                ->pluck('aggregate', 'band');

            // Scans with scores below 60 (prospects) and below 40 (priority)
            $lowScoreScans = Scan::where('status', 'completed')
                ->where(function ($q) {
                    $q->where('score', '<', 60);
                })
                ->with('website')
                ->latest()
                ->take(5)
                ->get();

            // Scraper watchdog feed: latest change-detection events across all
            // watched businesses + count of hot alerts in the last 7 days.
            $watchEvents = ScrapeWatchEvent::with('business')
                ->latest('id')
                ->take(8)
                ->get();

            $watchdogHotWeek = ScrapeWatchEvent::hot()
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            // Discovery: recent runs for the dashboard surface.
            $discoveryRuns = DiscoveryRun::latest()->take(5)->get();

            return view('admin.dashboard.index', [
                'scannerHealth' => $this->scannerHealth(),
                'stats' => $stats,
                'showAllStats' => $showAllStats,
                'recentEnquiries' => $recentEnquiries,
                'recentScans' => $recentScans,
                'pipelineStats' => $pipelineStats,
                'websitesBySector' => $websitesBySector,
                'scansByBand' => $scansByBand,
                'lowScoreScans' => $lowScoreScans,
                'watchEvents' => $watchEvents,
                'watchdogHotWeek' => $watchdogHotWeek,
                'discoveryRuns' => $discoveryRuns,
            ]);
        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString(),
            ]);

            // Return a graceful error view instead of crashing
            return view('admin.dashboard.error', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check whether the Python scanner engine is reachable, cached briefly so the
     * dashboard never stalls waiting on a dead service. When it is offline, the
     * last lines of its log file are included to aid diagnosis.
     */
    private function scannerHealth(): array
    {
        return Cache::remember('scanner.health', 30, function () {
            try {
                $url = config('services.scanner.url', 'http://localhost:5000');
                $online = Http::timeout(3)->get("{$url}/health")->successful();
            } catch (\Throwable) {
                $online = false;
            }

            return [
                'online' => $online,
                'checked_at' => now()->toIso8601String(),
                ...($online ? [] : ['log' => $this->scannerLogTail()]),
            ];
        });
    }

    /**
     * Last ~25 non-empty lines of the engine log, newest last, plus the name of
     * the file they came from — staff need to know WHICH log to open when the
     * panel is not enough to diagnose the problem.
     *
     * @return array{source: string, lines: array<int, string>}|null
     */
    private function scannerLogTail(int $lines = 25): ?array
    {
        $paths = [
            base_path('scanner-service.log'),
            base_path('scanner.log'),
        ];

        foreach ($paths as $path) {
            if (!is_file($path)) {
                continue;
            }

            $all = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            return [
                'source' => basename($path),
                'lines' => array_slice(is_array($all) ? $all : [], -$lines),
            ];
        }

        return null;
    }

    private static function countScannedWebsitesBelow(int $threshold): int
    {
        return Scan::where('status', 'completed')
            ->where('score', '<', $threshold)
            ->whereHas('website', fn($q) => $q->where('exclusion_status', 'active'))
            ->select(DB::raw('COUNT(DISTINCT website_id) as count'))
            ->pluck('count')
            ->first();
    }
}
