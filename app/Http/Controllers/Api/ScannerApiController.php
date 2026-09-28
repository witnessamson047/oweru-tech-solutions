<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scan;
use App\Models\Website;
use App\Services\ScannerClient;
use App\Support\UrlInput;
use Illuminate\Http\Request;

class ScannerApiController extends Controller
{
    /**
     * Request a scan via the Python scanner service.
     */
    public function scan(Request $request)
    {
        // Forgiving input: accept "abc.co.tz" just like "https://abc.co.tz"
        $url = UrlInput::normalize($request->input('url'));

        if (!$url) {
            return response()->json([
                'success' => false,
                'message' => UrlInput::friendlyError(),
            ], 422);
        }

        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['host'])) {
            return response()->json([
                'success' => false,
                'message' => UrlInput::friendlyError(),
            ], 422);
        }

        // Check exclusion list
        $website = Website::where('url', $url)->first();
        if ($website && $website->exclusion_status === 'excluded') {
            return response()->json([
                'success' => false,
                'message' => 'This website has requested not to be scanned.',
            ], 403);
        }

        // Create or get website record
        if (!$website) {
            $website = Website::create([
                'business_name' => $parsed['host'],
                'url' => $url,
                'status' => 'active',
            ]);
        }

        $scan = Scan::create([
            'website_id' => $website->id,
            'url' => $url,
            'status' => 'running',
            'source' => 'public',
        ]);

        $result = ScannerClient::make()->runScan($scan);

        if (!$result['ok']) {
            $unreachable = str_contains($scan->error_message ?? '', 'unreachable');

            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], $unreachable ? 503 : 500);
        }

        $scan->refresh();

        // Auto-create enquiry from public scan and link to website
        if ($scan->source === 'public' && !$website->enquiry_id) {
            $enquiry = \App\Models\Enquiry::create([
                'name' => $parsed['host'],
                'business_name' => $parsed['host'],
                'email' => '',
                'phone' => '',
                'country' => 'Tanzania',
                'package_name' => 'Website Health Report',
                'problem_description' => "Auto-created from public scan of {$url}. Score: {$scan->score}/100 ({$scan->band}).",
                'budget_range' => 'prefer_not_say',
                'stage' => 'new',
                'source' => 'scanner',
                'consent_given' => false,
            ]);

            $website->update(['enquiry_id' => $enquiry->id]);
            $scan->update(['enquiry_id' => $enquiry->id]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'scan_id' => $scan->id,
                'score' => $scan->score,
                'band' => $scan->band,
                // Doc §11: the FREE public scan shows the score + the 3 worst
                // findings; the 5-finding version is the full (paid) report.
                'findings' => $scan->results()
                    ->where('passed', false)
                    ->orderBy('points')
                    ->limit(3)
                    ->get(['check_name', 'area', 'finding_text', 'consequence']),
            ],
        ]);
    }

    /**
     * Get scan status/results.
     */
    public function getStatus($id)
    {
        $scan = Scan::with('results')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $scan->id,
                'status' => $scan->status,
                'score' => $scan->score,
                'band' => $scan->band,
                'findings' => $scan->results
                    ->where('passed', false)
                    ->sortBy('points')
                    ->take(3)
                    ->values()
                    ->map(fn ($r) => $r->only(['check_name', 'area', 'finding_text', 'consequence'])),
            ],
        ]);
    }

    /**
     * Receive callback from Python scanner (async mode).
     */
    public function callback(Request $request)
    {
        $validated = $request->validate([
            'scan_id' => 'required|exists:scans,id',
            'status' => 'required|in:completed,failed',
            'score' => 'nullable|integer|min:0|max:100',
            'results' => 'nullable|array',
        ]);

        $scan = Scan::findOrFail($validated['scan_id']);

        if ($validated['status'] === 'failed') {
            $scan->update([
                'status' => 'failed',
                'error_message' => $request->input('error', 'Scanner reported failure.'),
                'completed_at' => now(),
            ]);

            return response()->json(['success' => true]);
        }

        ScannerClient::make()->storeResults($scan, $validated['results'] ?? [], $validated['score'] ?? null);

        $scan->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
