<?php

namespace App\Http\Controllers;

use App\Models\Scan;
use App\Models\Enquiry;
use App\Models\Website;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ScannerController extends Controller
{
    public function index()
    {
        return view('scanner.index');
    }

    public function reportRequest(Request $request)
    {
        $validated = $request->validate([
            'scan_id' => 'required|exists:scans,id',
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'consent' => 'accepted',
        ]);

        $scan = Scan::findOrFail($validated['scan_id']);

        // Create enquiry from scanner request
        $enquiry = Enquiry::create([
            'name' => $validated['name'],
            'business_name' => $validated['business_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'country' => 'Tanzania',
            'package_name' => 'Website Health Report',
            'problem_description' => "Requested full website health report for: {$scan->url}",
            'budget_range' => 'prefer_not_say',
            'stage' => 'new',
            'source' => 'scanner',
            'consent_given' => true,
        ]);

        // Link the website and scan to the enquiry
        if ($scan->website_id) {
            $website = Website::find($scan->website_id);
            if ($website) {
                $website->update(['enquiry_id' => $enquiry->id]);
            }
        }

        // Alert staff about this scanner-sourced lead
        app(NotificationService::class)->newEnquiry($enquiry);

        return redirect()->route('scanner.index')
            ->with('success', 'Thank you! Your full report request has been submitted. We will send it to your email shortly.');
    }
}
