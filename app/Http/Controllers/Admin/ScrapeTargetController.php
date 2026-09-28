<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ScrapeTargetJob;
use App\Models\ScrapeTarget;
use App\Services\ScraperClient;
use Illuminate\Http\Request;

class ScrapeTargetController extends Controller
{
    public function index(Request $request)
    {
        $query = ScrapeTarget::query();

        if ($search = $request->input('search')) {
            $query->where('url', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $query->where('status', (int) $request->input('status'));
        }

        $targets = $query->latest('last_scraped_at')->paginate(20)->withQueryString();

        $stats = [
            'active' => ScrapeTarget::where('status', ScrapeTarget::STATUS_ACTIVE)->count(),
            'paused' => ScrapeTarget::where('status', ScrapeTarget::STATUS_PAUSED)->count(),
            'due' => ScrapeTarget::due()->count(),
        ];

        return view('admin.scrape-targets.index', compact('targets', 'stats'));
    }

    /**
     * Bulk add targets — one URL per line, or separated by spaces/commas.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'urls' => 'required|string',
        ]);

        $urls = preg_split('/[\s,]+/', trim($validated['urls'])) ?: [];
        $urls = array_values(array_unique(array_filter($urls)));

        // Host-level dedupe: abc.co.tz and www.abc.co.tz are the SAME site —
        // queue it once. abc.co.tz vs abc.com are DIFFERENT businesses (the
        // ending — .co.tz / .com — is part of the identity, both are kept).
        $existingHostKeys = ScrapeTarget::query()
            ->get()
            ->map(fn (ScrapeTarget $t) => ScrapeTarget::hostKey($t->url))
            ->filter()
            ->flip();

        $added = 0;
        $invalid = 0;
        $skipped = [];
        $queuedUrls = [];

        foreach ($urls as $raw) {
            $url = ScrapeTarget::normalizeUrl($raw);

            if (! $url) {
                $invalid++;
                continue;
            }

            $hostKey = ScrapeTarget::hostKey($url);

            if ($hostKey === null || $existingHostKeys->has($hostKey)) {
                $skipped[] = $url;
                continue;
            }

            ScrapeTarget::create([
                'url' => $url,
                'status' => ScrapeTarget::STATUS_ACTIVE,
            ]);
            $existingHostKeys->put($hostKey, true);
            $queuedUrls[] = $url;
            $added++;
        }

        $message = "{$added} target(s) queued for automatic scraping";
        if ($queuedUrls !== []) {
            $message .= ': ' . implode(', ', $queuedUrls);
        }
        if ($skipped !== []) {
            $message .= ' · ' . count($skipped) . ' duplicate site(s) skipped (already in the queue): ' . implode(', ', $skipped);
        }
        if ($invalid) {
            $message .= " · {$invalid} invalid URL(s) skipped.";
        }

        return redirect()->route('admin.scrape-targets.index')->with('success', $message);
    }

    public function pause(ScrapeTarget $target)
    {
        $target->update(['status' => ScrapeTarget::STATUS_PAUSED]);

        return redirect()->back()->with('success', 'Target paused.');
    }

    public function resume(ScrapeTarget $target)
    {
        $target->update(['status' => ScrapeTarget::STATUS_ACTIVE]);

        return redirect()->back()->with('success', 'Target resumed.');
    }

    public function runNow(Request $request, ScrapeTarget $target)
    {
        $result = ScraperClient::make()->scrape($target->url);

        $target->update([
            'last_scraped_at' => now(),
            'scrape_count' => $target->scrape_count + 1,
            'last_error' => $result['ok'] ? null : $result['message'],
        ]);

        return redirect()->back()->with(
            $result['ok'] ? 'success' : 'error',
            $result['ok']
                ? "Scraped {$target->url} — " . ($result['business']->business_name ?: 'saved.')
                : $result['message'],
        );
    }

    /**
     * Watchdog "Check All Now": queue a fresh scrape for EVERY active target
     * so staff can force a watchdog pass without waiting for the schedule.
     * Each scrape runs as its own queued job (ScrapeTargetJob) so one slow
     * or dead site cannot stall the others; results (watchdog events,
     * refreshed contacts/gaps/reviews) appear as the worker processes them.
     */
    public function checkAll(Request $request)
    {
        $targets = ScrapeTarget::where('status', ScrapeTarget::STATUS_ACTIVE)->get();

        if ($targets->isEmpty()) {
            return redirect()->back()->with('error', 'No active scrape targets to check.');
        }

        foreach ($targets as $target) {
            ScrapeTargetJob::dispatch($target);
        }

        return redirect()->back()->with(
            'success',
            "Watchdog check queued for {$targets->count()} target(s). Results appear on each business page as the worker runs — give it a minute or two.",
        );
    }

    public function destroy(ScrapeTarget $target)
    {
        $target->delete();

        return redirect()->back()->with('success', 'Target removed from the queue.');
    }
}
