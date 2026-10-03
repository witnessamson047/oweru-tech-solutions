<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Concerns\SortsListings;
use App\Jobs\ScanWebsiteJob;
use App\Models\Scan;
use App\Models\Website;
use App\Services\ScannerClient;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    use SortsListings;

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request, [
            'score' => 'Score',
            'band' => 'Band',
            'status' => 'Status',
            'created_at' => 'Newest',
        ], default: 'created_at', defaultDirection: 'desc');

        $query = Scan::with(['website', 'results']);

        if ($search = trim((string) $request->input('search'))) {
            $query->whereHas('website', function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($band = $request->input('band')) {
            $query->where('band', $band);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $this->applySort($query, $sort);

        $scans = $query->paginate($this->perPage($request, 20))->withQueryString();

        return view('admin.scans.index', [
            'scans' => $scans,
            'sort' => $sort,
            'stats' => [
                'total' => Scan::count(),
                'completed' => Scan::where('status', 'completed')->count(),
                'failed' => Scan::where('status', 'failed')->count(),
                'critical' => Scan::where('status', 'completed')->where('score', '<', 40)->count(),
            ],
        ]);
    }

    public function show(Scan $scan)
    {
        $scan->load(['website', 'results.check', 'results.recommendation', 'enquiry', 'report']);

        return view('admin.scans.show', compact('scan'));
    }

    /**
     * Trigger a fresh scan for a website (creates a new Scan record).
     */
    public function run(Request $request, Website $website)
    {
        $scan = Scan::create([
            'website_id' => $website->id,
            'url' => $website->url,
            'status' => 'running',
            'source' => 'admin',
        ]);

        $result = ScannerClient::make()->runScan($scan);

        return redirect()->route('admin.scans.show', $scan)
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Scan every (non-excluded) website in the list. Dispatches one queued
     * job per website so the request returns immediately; scans run in the
     * background via `php artisan queue:work`.
     */
    public function runBatch(Request $request)
    {
        $query = Website::where('exclusion_status', '!=', 'excluded');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $websites = $query->get();

        if ($websites->isEmpty()) {
            return redirect()->route('admin.websites.index')
                ->with('error', 'No websites match the current filters â€” nothing to scan.');
        }

        foreach ($websites as $website) {
            ScanWebsiteJob::dispatch($website);
        }

        $count = $websites->count();

        return redirect()->route('admin.scans.index')
            ->with('success', "Batch scan queued: {$count} website(s) will be scanned in the background. Refresh in a few minutes to see results.");
    }
}
