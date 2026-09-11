<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Scan;
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
                'new_enquiries' => Enquiry::where('stage', 'new')->count(),
                'total_scans' => Scan::where('status', 'completed')->count(),
                'websites' => Website::count(),
                'scored_websites' => Website::whereHas('latestScan', fn($q) => $q->whereNotNull('score'))->count(),
                'priority_prospects' => self::countScannedWebsitesBelow(40),
                'prospects' => self::countScannedWebsitesBelow(60) - self::countScannedWebsitesBelow(40),
                'excluded_websites' => Website::where('exclusion_status', 'excluded')->count(),
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

            // Website breakdown by sector
            $websitesBySector = Website::active()->get()->groupBy('sector')->map(fn($group) => $group->count());

            // Scans by band breakdown
            $scansByBand = Scan::where('status', 'completed')
                ->get()
                ->groupBy('band')
                ->map(fn($group) => $group->count());

            // Scans with scores below 60 (prospects) and below 40 (priority)
            $lowScoreScans = Scan::where('status', 'completed')
                ->where(function ($q) {
                    $q->where('score', '<', 60);
                })
                ->with('website')
                ->latest()
                ->take(5)
                ->get();

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
                ...($online ? [] : ['log_tail' => $this->scannerLogTail()]),
            ];
        });
    }

    /**
     * Last ~25 non-empty lines of the watchdog/engine log, newest last.
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

            return array_slice(is_array($all) ? $all : [], -$lines);
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
