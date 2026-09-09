@extends('layouts.public')

@section('title', 'Free Website Health Check - Oweru Tech Solutions')

@section('content')

{{-- Hero --}}
<section class="bg-black text-white py-16 lg:py-20">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <div class="inline-flex items-center gap-2 bg-yellow-500/20 border border-yellow-500/30 rounded-full px-4 py-1.5 text-sm font-medium text-yellow-300 mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Free Website Health Check
        </div>
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4">
            How Healthy Is Your Website?
        </h1>
        <p class="text-gray-300 text-lg max-w-2xl mx-auto mb-8">
            Our scanner checks your website's security, mobile experience, speed, and more.
            Get a score out of 100 and discover what's holding your site back.
        </p>

        {{-- Scanner Input --}}
        <form id="scanner-form" class="max-w-xl mx-auto">
            <div class="flex gap-2">
                <input type="url" id="scanner-url" name="url"
                    class="scanner-input flex-1"
                    placeholder="https://yourbusiness.co.tz"
                    required>
                <button type="submit" id="scan-btn" class="btn-accent text-base px-6 py-3 whitespace-nowrap">
                    🔍 Scan Website
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2">We only scan publicly accessible pages. No login or private data is accessed.</p>
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
<section class="py-12 bg-gray-50 min-h-[400px]">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div id="scanner-results"></div>

        {{-- Placeholder when no results --}}
        <div id="scanner-placeholder" class="text-center py-16">
            <div class="w-24 h-24 bg-yellow-50 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-yellow-100">
                <svg class="w-12 h-12 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <p class="text-gray-500 text-sm">Enter any website address above to get a free health scan with a score out of 100.</p>
        </div>
    </div>
</section>

{{-- How It Works --}}
<section class="py-16 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">How Our Scanner Works</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <span class="text-yellow-600 font-bold text-lg">1</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Enter Your URL</h3>
                <p class="text-sm text-gray-500">Type or paste your website address into the scanner above.</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <span class="text-yellow-600 font-bold text-lg">2</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Automated Analysis</h3>
                <p class="text-sm text-gray-500">We check security, mobile readiness, speed, links, trust signals, and more.</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <span class="text-yellow-600 font-bold text-lg">3</span>
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">Get Your Score</h3>
                <p class="text-sm text-gray-500">Receive a score out of 100 with actionable findings and recommendations.</p>
            </div>
        </div>
    </div>
</section>

{{-- What We Check --}}
<section class="py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">What We Check</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="card p-4">
                <div class="text-2xl mb-2">🔒</div>
                <h4 class="font-semibold text-gray-900 text-sm">Security</h4>
                <p class="text-xs text-gray-500 mt-1">SSL certificate, secure loading, no mixed content</p>
                <span class="badge badge-gray text-[10px] mt-2">20 points</span>
            </div>
            <div class="card p-4">
                <div class="text-2xl mb-2">📱</div>
                <h4 class="font-semibold text-gray-900 text-sm">Mobile Experience</h4>
                <p class="text-xs text-gray-500 mt-1">Responsive layout, readable text, tap-friendly buttons</p>
                <span class="badge badge-gray text-[10px] mt-2">20 points</span>
            </div>
            <div class="card p-4">
                <div class="text-2xl mb-2">⚡</div>
                <h4 class="font-semibold text-gray-900 text-sm">Speed</h4>
                <p class="text-xs text-gray-500 mt-1">Load time, page size, image optimization</p>
                <span class="badge badge-gray text-[10px] mt-2">15 points</span>
            </div>
            <div class="card p-4">
                <div class="text-2xl mb-2">⚙️</div>
                <h4 class="font-semibold text-gray-900 text-sm">Functionality</h4>
                <p class="text-xs text-gray-500 mt-1">Working links, contact forms, tappable phone/email</p>
                <span class="badge badge-gray text-[10px] mt-2">15 points</span>
            </div>
            <div class="card p-4">
                <div class="text-2xl mb-2">🔍</div>
                <h4 class="font-semibold text-gray-900 text-sm">Findability</h4>
                <p class="text-xs text-gray-500 mt-1">Page titles, meta descriptions, search presence</p>
                <span class="badge badge-gray text-[10px] mt-2">12 points</span>
            </div>
            <div class="card p-4">
                <div class="text-2xl mb-2">🛡️</div>
                <h4 class="font-semibold text-gray-900 text-sm">Trust Signals</h4>
                <p class="text-xs text-gray-500 mt-1">Company name, address, privacy policy, terms</p>
                <span class="badge badge-gray text-[10px] mt-2">10 points</span>
            </div>
            <div class="card p-4">
                <div class="text-2xl mb-2">🛒</div>
                <h4 class="font-semibold text-gray-900 text-sm">Commerce</h4>
                <p class="text-xs text-gray-500 mt-1">Online payment or booking functionality</p>
                <span class="badge badge-gray text-[10px] mt-2">5 points</span>
            </div>
            <div class="card p-4">
                <div class="text-2xl mb-2">🕐</div>
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
                    <span class="text-xs text-gray-500">I agree to Oweru processing my data per the <a href="#" class="text-yellow-600 underline">Privacy Policy</a>.</span>
                </div>
                <button type="submit" class="btn-accent w-full justify-center">Request Full Report</button>
            </div>
        </form>
    </div>
</div>

@endsection
