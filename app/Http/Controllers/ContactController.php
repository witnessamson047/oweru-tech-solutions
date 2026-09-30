<?php

namespace App\Http\Controllers;

use App\Models\Enquiry;
use App\Services\NotificationService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Simple contact page — deliberately lighter than the enquiry form:
     * name, email, and a message are all that's required.
     */
    public function create()
    {
        return view('pages.contact');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'message' => 'required|string|max:2000',
            // Honeypot: real users never fill this hidden field — bots do.
            'website' => 'prohibited',
        ]);

        $enquiry = Enquiry::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            // Simple questions may not have a business or a budget yet.
            'business_name' => $validated['business_name'] ?? null,
            'problem_description' => $validated['message'],
            'budget_range' => $validated['budget_range'] ?? null,
            'country' => 'Tanzania',
            'source' => 'contact',
            'consent_given' => true,
            'last_stage_changed_at' => now(),
        ]);

        // Reuse the existing staff notification flow.
        app(NotificationService::class)->newEnquiry($enquiry);

        return redirect()->route('contact.create')
            ->with('success', 'Thank you! Your message has been received — we usually reply within 24 business hours.');
    }
}
