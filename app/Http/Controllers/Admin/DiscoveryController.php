<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\DiscoveryJob;
use App\Models\DiscoveryLead;
use App\Models\DiscoveryRun;
use App\Services\DiscoveryService;
use Illuminate\Http\Request;

/**
 * Website Discovery (OSM/Overpass) — the zero-technical-exposure entry point:
 * staff pick a Location and a Category, press DISCOVER, and business websites
 * land in the existing auto-scraper queue. The probe takes 30-120s so the
 * POST runs it synchronously with generous PHP settings (mirrors the
 * ScanController pattern of raising limits around slow python work).
 */
class DiscoveryController extends Controller
{
    public function index(Request $request)
    {
        $runs = DiscoveryRun::latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => DiscoveryRun::count(),
            'failed' => DiscoveryRun::where('status', DiscoveryRun::STATUS_FAILED)->count(),
            'queued' => (int) DiscoveryRun::where('status', DiscoveryRun::STATUS_COMPLETED)->sum('queued_count'),
            'leads' => DiscoveryLead::count(),
            'new_leads' => DiscoveryLead::new()->count(),
        ];

        return view('admin.discovery.index', compact('runs', 'stats'));
    }

    /**
     * The no-website outreach list: businesses discovery found that have no
     * website at all — Oweru's "we'll build you one" prospects.
     */
    public function leads(Request $request)
    {
        $query = DiscoveryLead::query()->latest('created_at');

        if ($search = trim((string) $request->input('search'))) {
            $query->where(fn ($q) => $q
                ->where('business_name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%"));
        }

        if ($request->filled('status') && in_array($request->input('status'), [DiscoveryLead::STATUS_NEW, DiscoveryLead::STATUS_CONTACTED], true)) {
            $query->where('status', $request->input('status'));
        }

        $leads = $query->paginate(30)->withQueryString();

        $stats = [
            'total' => DiscoveryLead::count(),
            'new' => DiscoveryLead::new()->count(),
            'contacted' => DiscoveryLead::where('status', DiscoveryLead::STATUS_CONTACTED)->count(),
        ];

        return view('admin.discovery.leads', compact('leads', 'stats'));
    }

    public function markContacted(Request $request, DiscoveryLead $lead)
    {
        $lead->markContacted();

        return redirect()->back()->with('success', "'{$lead->business_name}' marked as contacted.");
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'city' => 'required|string|min:2|max:100',
            'category' => 'required|string|in:' . implode(',', array_keys(config('owers.discovery.categories'))),
        ]);

        $limit = (int) $request->input('limit', config('owers.discovery.default_limit'));
        $limit = min(max($limit, 50), (int) config('owers.discovery.max_limit'));

        // The run row is created NOW (visible as 'running' on the page) and
        // the probe executes on the queue worker: on this Windows machine,
        // python spawned from the serve process cannot resolve DNS
        // (WinSock per-process quirk), while the worker lineage — the same
        // one that runs every scrape and scan — works fine. Bonus: the
        // client gets instant feedback instead of a 2-minute frozen form.
        $run = DiscoveryService::make()->createRun(
            $validated['city'],
            $validated['category'],
            $limit,
            DiscoveryRun::SOURCE_ADMIN,
            $request->user()?->id,
        );

        DiscoveryJob::dispatch($run);

        return redirect()
            ->route('admin.discovery.index')
            ->with(
                'success',
                "Discovery for {$run->city} ({$run->category}) queued — this takes a minute or two. " .
                'Refresh this page to see the results as they land; new websites go straight into the auto-scraper queue.',
            );    }
}
