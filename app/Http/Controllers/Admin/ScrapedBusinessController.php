<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Models\Recommendation;
use App\Models\Scan;
use App\Models\ScrapedBusiness;
use App\Models\Website;
use App\Services\ScannerClient;
use App\Services\ScraperClient;
use App\Support\UrlInput;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScrapedBusinessController extends Controller
{
    /**
     * Scrape form + list of scraped businesses.
     */
    public function index(Request $request)
    {
        $query = ScrapedBusiness::query()->with(['website.latestScan', 'enquiry']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('website_url', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $businesses = $query->latest()->paginate(20)->withQueryString();

        // ONE recommendations load for the whole page — rows pass it into
        // mappedServices() so the index never runs per-row queries.
        $recommendations = Recommendation::query()->where('active', true)->get()->keyBy('check_name');

        return view('admin.scraped-businesses.index', [
            'businesses' => $businesses,
            'statuses' => ScrapedBusiness::STATUSES,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Handle the scrape form submission.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'url' => 'required|string|max:2048',
        ]);

        // Forgiving input: "abc.co.tz" works just like "https://abc.co.tz"
        $url = UrlInput::normalize($validated['url']);

        if (!$url) {
            return redirect()
                ->route('admin.scraped-businesses.index')
                ->with('error', UrlInput::friendlyError())
                ->withErrors(['url' => UrlInput::friendlyError()])
                ->withInput();
        }

        $result = ScraperClient::make()->scrape($url);

        if (!$result['ok']) {
            return redirect()
                ->route('admin.scraped-businesses.index')
                ->with('error', $result['message'])
                ->withInput();
        }

        return redirect()
            ->route('admin.scraped-businesses.show', $result['business'])
            ->with('success', $result['message']);
    }

    /**
     * Export the businesses list as CSV for outreach planning. Respects the
     * same search/status filters as the index page; mapped Oweru services
     * are resolved with ONE recommendations load for the whole export.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = ScrapedBusiness::query()->with(['website.latestScan', 'enquiry']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('website_url', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $recommendations = Recommendation::query()->where('active', true)->get()->keyBy('check_name');

        $filename = 'oweru-businesses-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query, $recommendations) {
            $out = fopen('php://output', 'w');

            // UTF-8 BOM so Excel opens Swahili/accented text correctly.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Business Name', 'Website', 'Scraped From', 'Status',
                'Email', 'Phone', 'Address', 'Services', 'About',
                'Prelim Gaps', 'Outreach Priority', 'Mapped Oweru Services',
                'Latest Scan Score', 'Latest Scan Band', 'Enquiry #',
                'Times Scraped', 'Last Scraped At',
            ]);

            $query->orderByDesc('id')->chunk(200, function ($businesses) use ($out, $recommendations) {
                foreach ($businesses as $business) {
                    // Neutralize CSV formula injection (=, +, -, @ prefixes
                    // could execute as formulas when opened in Excel).
                    $safe = fn ($value) => preg_match('/^[=+\-@]/', (string) $value)
                        ? "'" . $value
                        : $value;

                    $services = $business->mappedServices($recommendations);

                    fputcsv($out, [
                        $safe($business->business_name),
                        $safe($business->website_url),
                        $safe($business->source_url ?: $business->website_url),
                        $business->status,
                        $safe($business->email),
                        $safe($business->phone),
                        $safe($business->address),
                        $safe($business->services),
                        $safe($business->about),
                        $business->preliminaryGapCount(),
                        $business->outreachPriority(),
                        $services->pluck('service_type')->implode('; '),
                        $business->website?->latestScan?->score,
                        $business->website?->latestScan?->band,
                        $business->enquiry_id,
                        $business->scrape_count,
                        $business->last_scraped_at?->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * Show one scraped business with its extracted fields.
     */
    public function show(ScrapedBusiness $scrapedBusiness)
    {
        $scrapedBusiness->load(['website.latestScan', 'enquiry']);

        return view('admin.scraped-businesses.show', [
            'business' => $scrapedBusiness,
            'gapsWithRecs' => $scrapedBusiness->gapsWithRecommendations(),
        ]);
    }

    /**
     * Re-scrape this business right now: refreshes contact details, services,
     * reviews, preliminary gaps and feeds the watchdog (a manual re-scrape
     * against the stored snapshot is exactly what generates watchdog events).
     */
    public function rescrape(Request $request, ScrapedBusiness $scrapedBusiness)
    {
        $result = ScraperClient::make()->scrape($scrapedBusiness->website_url);

        return redirect()
            ->route($result['ok'] ? 'admin.scraped-businesses.show' : 'admin.scraped-businesses.index', $result['ok'] ? $result['business'] : [])
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Send the scraped business to the existing health scanner: create (or
     * reuse) the Website record, then run a scan synchronously like
     * ScanController::run does.
     */
    public function runHealthScan(Request $request, ScrapedBusiness $scrapedBusiness)
    {
        // Fail fast with an actionable message when the engine is down.
        // (Skipped under testing: keep tests hermetic — no real network calls.)
        $client = ScannerClient::make();
        if (! app()->environment('testing') && ! $client->engineOnline()) {
            return redirect()
                ->route('admin.scraped-businesses.show', $scrapedBusiness)
                ->with('error', 'Scanner engine is OFFLINE — start it with start.bat (or C:\\python312\\python.exe scanner\\scanner.py), then run the scan again.');
        }

        $website = Website::firstOrCreate(
            ['url' => $scrapedBusiness->website_url],
            [
                'business_name' => $scrapedBusiness->business_name ?: $scrapedBusiness->website_url,
                'status' => 'active',
                'exclusion_status' => 'active',
            ]
        );

        $scan = Scan::create([
            'website_id' => $website->id,
            'url' => $website->url,
            'status' => 'running',
            'source' => 'admin',
        ]);

        $result = ScannerClient::make()->runScan($scan);

        if ($result['ok']) {
            $scrapedBusiness->update([
                'status' => 'scanned',
                'website_id' => $website->id,
            ]);
        }

        return redirect()->route('admin.scans.show', $scan)
            ->with($result['ok'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Convert the scraped business into a pipeline enquiry (source = outreach).
     */
    public function addToLeads(Request $request, ScrapedBusiness $scrapedBusiness)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'problem_description' => 'nullable|string',
        ]);

        if ($scrapedBusiness->enquiry_id && Enquiry::find($scrapedBusiness->enquiry_id)) {
            return redirect()->route('admin.enquiries.show', $scrapedBusiness->enquiry_id)
                ->with('success', 'This business is already in the leads pipeline.');
        }

        $enquiry = Enquiry::create([
            'name' => $validated['name'] ?? $scrapedBusiness->business_name ?: 'Unknown contact',
            'business_name' => $scrapedBusiness->business_name ?: '',
            'email' => $scrapedBusiness->email,
            'phone' => $scrapedBusiness->phone ?: '',
            'package_name' => null,
            'problem_description' => $validated['problem_description']
                ?? ($scrapedBusiness->about ?: 'Outreach lead from scraped business website.'),
            'budget_range' => 'Prefer not to say',
            'required_date' => null,
            'stage' => 'new',
            'source' => 'outreach',
            'notes' => 'Created from Scraper V1 for ' . $scrapedBusiness->website_url,
        ]);

        $scrapedBusiness->update([
            'status' => 'lead',
            'enquiry_id' => $enquiry->id,
        ]);

        return redirect()->route('admin.enquiries.show', $enquiry)
            ->with('success', 'Scraped business added to the leads pipeline.');
    }
}
