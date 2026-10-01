@extends('layouts.public')

@section('title', 'Free Website Health Check - Oweru Tech Solutions')

@section('content')

{{-- Hero --}}
<section class="scanner-hero relative text-white py-8 sm:py-10 overflow-hidden">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 bg-yellow-500/20 border border-yellow-500/30 rounded-full px-4 py-1.5 text-sm font-medium text-yellow-300 mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Free Website Health Check
        </div>
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-3">
            How Healthy Is Your Website?
        </h1>
        <p class="text-gray-200 text-base sm:text-lg max-w-2xl mx-auto mb-5">
            Our scanner checks your website's security, mobile experience, speed, and more.
            Get a score out of 100 and discover what's holding your site back.
        </p>

        {{-- Scanner Input --}}
        <form id="scanner-form" class="max-w-xl mx-auto">
            <div class="flex flex-col sm:flex-row gap-2">
                {{-- type="text" + inputmode="url" on purpose: the browser's URL
                     field rejects human input like "yourbusiness.co.tz" (no https://).
                     The server accepts both forms, so visitors can type either. --}}
                <input type="text" id="scanner-url" name="url" inputmode="url" autocomplete="url"
                    class="scanner-input flex-1 min-w-0"
                    placeholder="yourbusiness.co.tz"
                    required>
                <button type="submit" id="scan-btn" class="btn-accent w-full sm:w-auto justify-center text-base px-6 py-3 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Scan Website
                </button>
            </div>
            <p class="text-xs text-gray-300 mt-2">We only scan publicly accessible pages. No login or private data is accessed.</p>
        </form>

        {{-- Loading State --}}
        <div id="scanner-loader" class="hidden mt-8">
            <div class="flex items-center justify-center gap-3 text-gray-300">
                <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="font-medium">Analyzing your website... This may take 30-60 seconds</span>
            </div>
            <div class="max-w-md mx-auto mt-4">
                <div class="progress-bar">
                    <div class="progress-bar-fill bg-yellow-500 animate-pulse-slow" style="width: 60%"></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Results Container --}}
<section id="scanner-results-section" class="bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div id="scanner-results"></div>
    </div>
</section>

{{-- What We Check --}}
<section class="scanner-checks-section bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-5">What We Check</h2>
        <div class="scanner-check-grid">
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Security</h4>
                <p class="text-xs text-gray-500 mt-1">SSL certificate, secure loading, no mixed content</p>
                <span class="badge badge-gray text-[10px] mt-2">20 points</span>
            </div>
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Mobile Experience</h4>
                <p class="text-xs text-gray-500 mt-1">Responsive layout, readable text, tap-friendly buttons</p>
                <span class="badge badge-gray text-[10px] mt-2">20 points</span>
            </div>
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Speed</h4>
                <p class="text-xs text-gray-500 mt-1">Load time, page size, image optimization</p>
                <span class="badge badge-gray text-[10px] mt-2">15 points</span>
            </div>
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Functionality</h4>
                <p class="text-xs text-gray-500 mt-1">Working links, contact forms, tappable phone/email</p>
                <span class="badge badge-gray text-[10px] mt-2">15 points</span>
            </div>
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Findability</h4>
                <p class="text-xs text-gray-500 mt-1">Page titles, meta descriptions, search presence</p>
                <span class="badge badge-gray text-[10px] mt-2">12 points</span>
            </div>
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Trust Signals</h4>
                <p class="text-xs text-gray-500 mt-1">Company name, address, privacy policy, terms</p>
                <span class="badge badge-gray text-[10px] mt-2">10 points</span>
            </div>
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Commerce</h4>
                <p class="text-xs text-gray-500 mt-1">Online payment or booking functionality</p>
                <span class="badge badge-gray text-[10px] mt-2">5 points</span>
            </div>
            <div class="card p-4">
                <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center mb-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h4 class="font-semibold text-gray-900 text-sm">Freshness</h4>
                <p class="text-xs text-gray-500 mt-1">Recent content updates, current copyright year</p>
                <span class="badge badge-gray text-[10px] mt-2">3 points</span>
            </div>
        </div>
    </div>
</section>

{{-- Report Request Modal --}}
<div id="report-modal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 relative">
        <button onclick="document.getElementById('report-modal').classList.add('hidden')" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <h3 class="text-lg font-bold text-gray-900 mb-1">Get Your Full Report</h3>
        <p class="text-sm text-gray-500 mb-4">Enter your details to receive a comprehensive one-page PDF report with all findings and recommendations.</p>
        <form method="POST" action="{{ route('scanner.report-request') }}">
            @csrf
            <input type="hidden" name="scan_id" id="report-scan-id">
            <div class="space-y-3">
                <input type="text" name="name" placeholder="Full Name *" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="text" name="business_name" placeholder="Business Name *" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="email" name="email" placeholder="Email Address *" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <input type="tel" name="phone" placeholder="Phone Number *" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <div class="flex items-start gap-2">
                    <input type="checkbox" name="consent" required class="mt-1 h-4 w-4 text-yellow-600 rounded">
                    <span class="text-xs text-gray-500">I agree to Oweru using my details to prepare and send this report.</span>
                </div>
                <button type="submit" class="btn-accent w-full justify-center">Request Full Report</button>
            </div>
        </form>
    </div>
</div>

<style>
    .scanner-hero {
        background: #17372f;
    }

    #scanner-results-section:has(#scanner-results:empty) {
        display: none;
    }

    #scanner-results-section:has(#scanner-results:not(:empty)) {
        padding: 20px 0;
    }

    .scanner-checks-section {
        padding: 26px 0 34px;
    }

    .scanner-check-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .scanner-check-grid .card {
        padding: 12px;
    }

    .scanner-check-grid .card > div:first-child {
        width: 34px;
        height: 34px;
        margin-bottom: 8px;
        border-radius: 8px;
    }

    @media (max-width: 900px) {
        .scanner-check-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 380px) {
        .scanner-check-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@endsection
