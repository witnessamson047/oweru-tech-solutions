<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Models\ServicePackage;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class EnquiryController extends Controller
{
    public function create()
    {
        $selectedPackage = null;
        $allPackages = ServicePackage::active()->orderBy('group')->get()->groupBy('group');

        if ($slug = request('package')) {
            $selectedPackage = ServicePackage::where('slug', $slug)->first();
        }

        return view('pages.enquiry', [
            'selectedPackage' => $selectedPackage,
            'allPackages' => $allPackages,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'country' => 'required|string|max:100',
            'package_id' => 'nullable|exists:service_packages,id',
            'package_name' => 'nullable|string|max:255',
            'problem_description' => 'required|string',
            'current_cost' => 'nullable|string|max:255',
            'budget_range' => 'required|string',
            'required_date' => 'nullable|date',
            'consent' => 'accepted',
        ]);

        // Resolve package name if ID provided
        if (!empty($validated['package_id']) && empty($validated['package_name'])) {
            $pkg = ServicePackage::find($validated['package_id']);
            $validated['package_name'] = $pkg?->name;
        }

        $validated['consent_given'] = true;
        unset($validated['consent']);

        $validated['last_stage_changed_at'] = now();

        $enquiry = Enquiry::create($validated);

        // Alert staff about the new lead (email + notification record)
        app(NotificationService::class)->newEnquiry($enquiry);

        return redirect()->route('home')
            ->with('success', 'Thank you! Your enquiry has been submitted. We will contact you within 24 hours.');
    }
}
