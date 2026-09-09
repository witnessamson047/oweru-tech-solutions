<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ScanWebsiteJob;
use App\Models\Scan;
use App\Models\Website;
use App\Services\ScannerClient;
use Illuminate\Http\Request;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $query = Scan::with(['website', 'results']);

        if ($search = $request->input('search')) {
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

        $scans = $query->latest()->paginate(20);

        return view('admin.scans.index', compact('scans'));
    }

    public function show(Scan $scan)
    {
        $scan->load(['website', 'results.check', 'enquiry', 'report']);

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
                ->with('error', 'No websites match the current filters — nothing to scan.');
        }

        foreach ($websites as $website) {
            ScanWebsiteJob::dispatch($website);
        }

        $count = $websites->count();

        return redirect()->route('admin.scans.index')
            ->with('success', "Batch scan queued: {$count} website(s) will be scanned in the background. Refresh in a few minutes to see results.");
    }
}
