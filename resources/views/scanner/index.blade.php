@extends('layouts.public')

@section('title', 'Free Website Health Check - Oweru Tech Solutions')

@section('content')

{{-- Hero --}}
<section class="relative bg-black text-white py-16 lg:py-20 overflow-hidden">
    <img src="{{ asset('images/hero/scanner-analytics.jpg') }}" alt="Website performance report with charts on a laptop screen"
        class="absolute inset-0 h-full w-full object-cover" loading="eager" />
    <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/75 to-black"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative text-on-photo">
        <div class="inline-flex items-center gap-2 bg-yellow-500/20 border border-yellow-500/30 rounded-full px-4 py-1.5 text-sm font-medium text-yellow-300 mb-6">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            Free Website Health Check
        </div>
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-4">
            How Healthy Is Your Website?
        </h1>
        <p class="text-gray-200 text-lg max-w-2xl mx-auto mb-8">
            Our scanner checks your website's security, mobile experience, speed, and more.
            Get a score out of 100 and discover what's holding your site back.
        </p>

        {{-- Scanner Input --}}
        <form id="scanner-form" class="max-w-xl mx-auto">
            <div class="flex gap-2">
                {{-- type="text" + inputmode="url" on purpose: the browser's URL
                     field rejects human input like "yourbusiness.co.tz" (no https://).
                     The server accepts both forms, so visitors can type either. --}}
                <input type="text" id="scanner-url" name="url" inputmode="url" autocomplete="url"
                    class="scanner-input flex-1"
                    placeholder="yourbusiness.co.tz"
                    required>
                <button type="submit" id="scan-btn" class="btn-accent text-base px-6 py-3 whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Scan Website
                </button>
            </div>
            <p class="text-xs text-gray-300 mt-2">We only scan publicly accessible pages. No login or private data is accessed.</p>
            <div class="flex flex-wrap items-center justify-center gap-4 mt-3 text-[11px] text-gray-300">
                <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> No signup required</span>
                <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> Results in about 60 seconds</span>
                <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg> 23 checks across 8 areas</span>
            </div>
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
                <h3 class="font-semibold text-gray-900 mb-1">Get Your Score</h3>                <p class="text-sm text-gray-500">Receive a score out of 100 with actionable findings and recommendations.</p>
            </div>
        </div>
    </div>
</section>

{{-- Work Showcase Carousel --}}
<section class="pb-4 pt-2 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div data-carousel data-interval="5000" aria-roledescription="carousel" aria-label="Examples of our work"
            class="group relative overflow-hidden rounded-2xl border border-gray-200 shadow-xl">
            <div class="relative aspect-[16/9]">
                @foreach([
                    ['src' => asset('images/hero/scanner-analytics.jpg'), 'alt' => 'Website performance report with charts on a laptop screen', 'caption' => 'Real diagnostics — a sample of the scorecard you will receive'],
                    ['src' => asset('images/hero/developer-coding.jpg'), 'alt' => 'Oweru developer writing code on a laptop', 'caption' => 'Fixes implemented by our own developers, not outsourced'],
                    ['src' => asset('images/services/mobile-web-development.jpg'), 'alt' => 'Responsive website shown on laptop and phone', 'caption' => 'We make sites work beautifully on every screen'],
                    ['src' => asset('images/services/crm-solutions.jpg'), 'alt' => 'Sales analytics dashboard on a monitor', 'caption' => 'From findings to tools that grow your revenue'],
                    ['src' => asset('images/hero/team-collaboration.jpg'), 'alt' => 'Oweru team collaborating around a table', 'caption' => 'A local team that explains everything in plain language'],
                ] as $slide)
                    <figure data-hero-slide class="absolute inset-0 opacity-0 transition-opacity duration-700 ease-in-out [&.is-active]:opacity-100 {{ $loop->first ? 'is-active' : '' }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                        <img src="{{ $slide['src'] }}" alt="{{ $slide['alt'] }}" class="h-full w-full object-cover" loading="lazy" />
                        <figcaption class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/45 to-transparent pt-10 pb-4 px-5">
                            <span class="block text-sm font-semibold text-yellow-300 text-on-photo">{{ $slide['caption'] }}</span>
                        </figcaption>
                    </figure>
                @endforeach

                <button type="button" data-hero-prev aria-label="Previous slide" class="absolute left-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/45 text-white opacity-60 backdrop-blur transition hover:bg-yellow-500 hover:text-black focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 md:opacity-0 md:group-hover:opacity-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
                </button>
                <button type="button" data-hero-next aria-label="Next slide" class="absolute right-3 top-1/2 z-10 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/45 text-white opacity-60 backdrop-blur transition hover:bg-yellow-500 hover:text-black focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 md:opacity-0 md:group-hover:opacity-100">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                </button>

                <div class="absolute bottom-3 left-1/2 z-10 flex -translate-x-1/2 gap-2" role="tablist" aria-label="Choose slide">
                    @foreach(range(1, 5) as $i)
                        <button type="button" data-hero-dot role="tab" aria-label="Go to slide {{ $i }}" class="h-2 w-2 rounded-full bg-white/40 transition-all duration-300 hover:bg-white/70 [&.is-active]:w-5 [&.is-active]:bg-yellow-400"></button>
                    @endforeach
                </div>
            </div>
        </div>
        <p class="text-center text-xs text-gray-400 mt-3">A peek at the team and work behind every scan.</p>
    </div>
</section>


{{-- What We Check --}}
<section class="py-16 bg-gray-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-10">What We Check</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                    <span class="text-xs text-gray-500">I agree to Oweru processing my data per the <a href="#" class="text-yellow-600 underline">Privacy Policy</a>.</span>
                </div>
                <button type="submit" class="btn-accent w-full justify-center">Request Full Report</button>
            </div>
        </form>
    </div>
</div>

@endsection
