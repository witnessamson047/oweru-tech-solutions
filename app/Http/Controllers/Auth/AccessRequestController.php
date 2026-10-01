<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Enquiry;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccessRequestController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'reason' => ['required', 'string', 'min:20', 'max:2000'],
            'consent' => ['accepted'],
            'website' => ['prohibited'],
        ]);

        $enquiry = Enquiry::create([
            'name' => $validated['name'],
            'business_name' => 'Admin access request',
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: 'Not provided',
            'country' => 'Tanzania',
            'problem_description' => 'Admin access request: ' . $validated['reason'],
            'stage' => 'new',
            'source' => 'contact',
            'consent_given' => true,
            'last_stage_changed_at' => now(),
        ]);

        app(NotificationService::class)->newEnquiry($enquiry);

        return redirect()->route('register')->with('success', __('auth.register.received'));
    }
}