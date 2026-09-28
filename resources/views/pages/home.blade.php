@extends('layouts.public')

@section('title', 'Oweru Tech Solutions - Professional Digital Services')

@section('content')

{{-- Slide deck: every <section class="slide ..."> below is a full-screen slide --}}
<div class="slide-deck" id="slide-deck">

{{-- ============================================================
     SLIDE 1 — HERO — full-bleed shifting image background with copy overlay
============================================================ --}}
<section class="slide relative overflow-hidden bg-gray-900" id="hero">

    @php
        $heroSlides = [
            ['src' => asset('images/services/header-consultation.jpg'), 'alt' => 'Oweru team reviewing a client project together'],
            ['src' => asset('images/hero/team-collaboration.jpg'), 'alt' => 'Oweru team collaborating around a table'],
            ['src' => asset('images/hero/developer-coding.jpg'), 'alt' => 'Oweru developer writing code on a laptop'],
            ['src' => asset('images/services/software-development.jpg'), 'alt' => 'Developer writing code across two monitors'],
            ['src' => asset('images/services/network-design.jpg'), 'alt' => 'Technician configuring network equipment'],
        ];
    @endphp

    {{-- Full-bleed shifting image background --}}
    <div data-carousel data-interval="5000" aria-roledescription="carousel" aria-label="Examples of our work" class="absolute inset-0">
        @foreach($heroSlides as $slide)
            <figure data-hero-slide class="absolute inset-0 opacity-0 transition-opacity duration-700 ease-in-out [&.is-active]:opacity-100 {{ $loop->first ? 'is-active' : '' }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                <img src="{{ $slide['src'] }}" alt="{{ $slide['alt'] }}" class="h-full w-full object-cover scale-105 blur-[2px]" />
            </figure>
        @endforeach
    </div>

    {{-- Readability overlay so the copy stays legible over any photo --}}
    <div class="absolute inset-0 bg-gradient-to-b from-black/85 via-black/70 to-black/90"></div>
    <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(212, 175, 55, 0.18) 0%, transparent 70%);"></div>
    <div class="absolute -bottom-32 -left-32 w-[28rem] h-[28rem] rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(212, 175, 55, 0.12) 0%, transparent 70%);"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-16 pb-16 lg:pt-24 lg:pb-20">

            {{-- Copy --}}
            <div class="text-center">
                <div class="inline-flex items-center gap-2 bg-white/10 ring-1 ring-white/25 backdrop-blur rounded-full px-4 py-1.5 text-xs sm:text-sm font-semibold text-yellow-300 mb-6">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full animate-pulse"></span>
                    Trusted by 50+ Businesses Across East Africa
                </div>

                <h1 class="text-4xl md:text-5xl lg:text-[3.6rem] font-black mb-6 leading-[1.05] tracking-tight text-white text-on-photo uppercase">
                    <span class="block">Digital Solutions</span>
                    <span class="block mt-1 text-yellow-400">That Drive</span>
                    <span class="block mt-1">Real Growth</span>
                </h1>

                <p class="text-base md:text-lg text-gray-200 mb-8 leading-relaxed max-w-xl mx-auto text-on-photo">
                    We help businesses build a strong online presence, attract the right audience,
                    and achieve measurable results — from a free website health scan to custom
                    software, CRMs and complete digital transformation.
                </p>

                <div class="flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('scanner.index') }}" class="btn-accent text-sm px-6 py-3 shadow-lg shadow-yellow-500/30">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Free Website Check
                    </a>
                    <a href="{{ route('packages.index') }}" class="btn-outline-light text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Services &amp; Pricing
                    </a>
                </div>

                <div class="mt-8 flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs sm:text-sm font-medium text-gray-200 text-on-photo">
                    <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Free Consultation</span>
                    <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> No Hidden Fees</span>
                    <span class="flex items-center gap-1.5"><svg class="w-3.5 h-3.5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 24/7 Support</span>
                </div>
            </div>

        {{-- Trust bar — frosted dark cards, readable over the shifting photo --}}
        <div class="mt-16 lg:mt-20 grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="flex items-center gap-3 rounded-2xl bg-black/45 backdrop-blur ring-1 ring-white/20 shadow-lg px-5 py-4">
                <div class="w-10 h-10 bg-yellow-400/20 rounded-xl flex items-center justify-center flex-shrink-0"><svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
                <div>
                    <div class="text-xl font-extrabold text-white leading-none">100+</div>
                    <div class="text-[11px] text-gray-300 mt-1">Projects Delivered</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl bg-black/45 backdrop-blur ring-1 ring-white/20 shadow-lg px-5 py-4">
                <div class="w-10 h-10 bg-yellow-400/20 rounded-xl flex items-center justify-center flex-shrink-0"><svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                <div>
                    <div class="text-xl font-extrabold text-white leading-none">50+</div>
                    <div class="text-[11px] text-gray-300 mt-1">Businesses Served</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl bg-black/45 backdrop-blur ring-1 ring-white/20 shadow-lg px-5 py-4">
                <div class="w-10 h-10 bg-yellow-400/20 rounded-xl flex items-center justify-center flex-shrink-0"><svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg></div>
                <div>
                    <div class="text-xl font-extrabold text-white leading-none">99.9%</div>
                    <div class="text-[11px] text-gray-300 mt-1">Uptime Guarantee</div>
                </div>
            </div>
            <div class="flex items-center gap-3 rounded-2xl bg-black/45 backdrop-blur ring-1 ring-white/20 shadow-lg px-5 py-4">
                <div class="w-10 h-10 bg-yellow-400/20 rounded-xl flex items-center justify-center flex-shrink-0"><svg class="w-5 h-5 text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg></div>
                <div>
                    <div class="text-xl font-extrabold text-white leading-none">24/7</div>
                    <div class="text-[11px] text-gray-300 mt-1">Support Available</div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     HOW IT WORKS — 4 steps with connector
============================================================ --}}
<section class="slide py-14 lg:py-20 bg-gray-50" id="how-it-works">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">How It Works</span>
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 uppercase tracking-tight">From First Scan to Working With Us</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 relative">
            <div class="hidden md:block absolute top-6 left-[12%] right-[12%] h-0.5 bg-gradient-to-r from-yellow-200 via-yellow-400 to-yellow-200"></div>
            @foreach([
                ['icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'title' => 'Scan Your Site', 'desc' => 'Run the free check — 23 automated checks across 8 areas score your site out of 100.'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'title' => 'Get Your Scorecard', 'desc' => 'See every finding, what it means for your business, and the exact fix it needs.'],
                ['icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'title' => 'Talk It Through', 'desc' => 'A free consultation maps the fixes to your goals and budget — zero pressure.'],
                ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Watch It Grow', 'desc' => 'We implement the improvements and you track the score climb on every re-scan.'],
            ] as $step)
                <div class="text-center relative">
                    <div class="w-12 h-12 mx-auto mb-3 bg-yellow-500 rounded-xl flex items-center justify-center shadow-lg shadow-yellow-500/30 ring-4 ring-gray-50">
                        <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/></svg>
                    </div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1">{{ $loop->iteration }}. {{ $step['title'] }}</h3>
                    <p class="text-gray-500 text-xs leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ route('scanner.index') }}" class="btn-accent text-sm">
                Start With Step 1 — It's Free
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     SERVICES — one package per row, full width, with photos
============================================================ --}}
<section class="slide slide-tall py-14 lg:py-16 bg-white" id="services">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">Our Services</span>
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2 uppercase tracking-tight">Solutions Tailored to <span class="text-yellow-600">Your Business</span></h2>
            <p class="text-gray-500 text-sm max-w-xl mx-auto mb-5">From simple websites to complex enterprise systems — transparent pricing, no surprises.</p>
            <div class="flex items-center justify-center gap-2">
                <span class="text-xs font-medium {{ !$currency || $currency === 'TZS' ? 'text-yellow-600' : 'text-gray-400' }}">TZS</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="currency-toggle" class="sr-only peer" {{ $currency === 'USD' ? 'checked' : '' }}>
                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:shadow after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
                </label>
                <span class="text-xs font-medium {{ $currency === 'USD' ? 'text-yellow-600' : 'text-gray-400' }}">USD</span>
            </div>
        </div>

        @php
            // Matches the image mapping used on the packages page.
            $packageImages = [
                'software-development' => ['image' => 'images/services/software-development.jpg', 'alt' => 'Developer writing code across two monitors'],
                'mobile-web-development' => ['image' => 'images/services/mobile-web-development.jpg', 'alt' => 'Responsive website design displayed on laptop and phone'],
                'crm-solutions' => ['image' => 'images/services/crm-solutions.jpg', 'alt' => 'Sales dashboard with charts on a computer screen'],
                'network-design' => ['image' => 'images/services/network-design.jpg', 'alt' => 'Technician configuring network equipment'],
            ];
            $defaultPackageImage = ['image' => 'images/hero/team-collaboration.jpg', 'alt' => 'Oweru team collaborating around a table'];
        @endphp

        {{-- Horizontal highlight row: 3 cards side by side (like the poster's services band) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            @foreach($highlightedPackages as $package)
                @php
                    $meta = $packageImages[$package->slug] ?? $defaultPackageImage;
                    $categoryLabel = match($package->group) {
                        'individuals' => 'Individuals',
                        'sme' => 'SMEs',
                        'corporate' => 'Corporate',
                        default => ucfirst($package->group ?? 'All'),
                    };
                @endphp
                <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col {{ $package->is_featured ? 'ring-2 ring-yellow-400 border-transparent' : '' }}">
                    {{-- Whole card links to the package's pricing section on the services page --}}
                    <a href="{{ route('packages.index') }}#{{ $package->serviceLine?->slug ?? '' }}" class="absolute inset-0 z-0" aria-label="See pricing for {{ $package->name }} on the services page"></a>
                    @if($package->is_featured)
                        <div class="absolute top-3 right-3 z-10">
                            <span class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow inline-flex items-center gap-1">
                                <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Recommended
                            </span>
                        </div>
                    @endif

                    {{-- Compact banner image --}}
                    <div class="relative">
                        <img src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="w-full h-36 object-cover group-hover:scale-[1.04] transition-transform duration-500">
                        <span class="absolute bottom-2 left-3 bg-black/80 text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-1 rounded-full">{{ $categoryLabel }}</span>
                    </div>

                    <div class="p-5 flex flex-col flex-1">
                        <h4 class="text-base font-bold text-gray-900 mb-1">{{ $package->name }}</h4>
                        <p class="text-sm text-gray-500 leading-relaxed line-clamp-2 flex-1">{{ $package->description }}</p>

                        <div class="flex items-baseline gap-2 mt-4">
                            <span class="text-xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                            <span class="text-xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                            <span class="text-[11px] text-gray-400">· {{ $package->delivery_days }}-day delivery</span>
                        </div>

                        <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="relative z-10 {{ $package->is_featured ? 'btn-accent' : 'btn-outline' }} w-full justify-center py-2.5 text-xs mt-4">Get Started →</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('packages.index') }}" class="btn-accent text-sm px-8 py-3 shadow-lg shadow-yellow-500/30">
                View All Services &amp; Pricing
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            <p class="text-xs text-gray-400 mt-2">All {{ $individualPackages->count() + $smePackages->count() + $corporatePackages->count() }} packages · TZS / USD switchable · care plans included</p>
        </div>
    </div>
</section>

{{-- ============================================================
     CRM SHOWCASE — light split: copy left, real photo right
============================================================ --}}
<section class="slide py-14 lg:py-20 bg-gray-50" id="crm-showcase">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 items-center">
            <div>
                <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">CRM Solution</span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-3 leading-tight uppercase tracking-tight">Take Control of Your <span class="text-yellow-600">Customer Relationships</span></h2>
                <p class="text-gray-600 text-base mb-5">Stop losing leads. Our custom CRM tracks every interaction, automates follow-ups, and grows your sales pipeline.</p>
                <div class="space-y-3 mb-7">
                    <div class="flex items-start gap-2">
                        <div class="w-5 h-5 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-2.5 h-2.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                        <div><p class="font-semibold text-gray-900 text-sm">Track Every Lead</p><p class="text-gray-500 text-xs">All leads captured in one place.</p></div>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="w-5 h-5 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-2.5 h-2.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                        <div><p class="font-semibold text-gray-900 text-sm">Automate Follow-ups</p><p class="text-gray-500 text-xs">Set it and forget it.</p></div>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="w-5 h-5 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-2.5 h-2.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                        <div><p class="font-semibold text-gray-900 text-sm">Detailed Analytics</p><p class="text-gray-500 text-xs">Insights for better decisions.</p></div>
                    </div>
                </div>
                <a href="{{ route('enquiry.create', ['package' => 'digital-transformation']) }}" class="btn-accent text-sm">Get CRM Solution →</a>
            </div>
            <div class="relative">
                <div class="absolute -inset-4 bg-yellow-200 rounded-[2rem] blur-2xl opacity-50 pointer-events-none"></div>
                <div class="relative rounded-[2rem] overflow-hidden shadow-2xl ring-1 ring-black/5">
                    <img src="{{ asset('images/services/crm-solutions.jpg') }}" alt="Sales analytics dashboard on a monitor" class="w-full h-72 sm:h-80 object-cover">
                </div>
                <div class="absolute -bottom-4 -right-3 sm:-right-5 bg-gray-900 text-white px-4 py-2.5 rounded-xl text-xs font-bold shadow-lg flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    From TZS 1.5M
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CARE PLANS — centered 3-up
============================================================ --}}
<section class="slide py-14 lg:py-20 bg-white" id="care-plans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">Ongoing Support</span>
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-3 uppercase tracking-tight">Monthly Care Plans</h2>
            <p class="text-gray-500 text-base max-w-xl mx-auto">Keep your digital systems running smoothly with our maintenance and support plans.</p>
        </div>

        @if($carePlans->isNotEmpty())
            {{-- Horizontal 3-up grid (matches the services row above) --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @foreach($carePlans as $plan)
                    <div class="relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-6 text-center flex flex-col items-center {{ $plan->is_featured ? 'ring-2 ring-yellow-400 border-transparent' : '' }}">
                        @if($plan->is_featured)
                            <div class="absolute top-3 right-3">
                                <span class="bg-yellow-500 text-black text-[10px] font-bold px-3 py-1 rounded-full shadow inline-flex items-center gap-1">
                                    <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                    Best Value
                                </span>
                            </div>
                        @endif

                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $plan->name }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed flex-1">{{ $plan->description }}</p>

                        <div class="flex items-baseline justify-center gap-1 mt-4">
                            <span class="text-2xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($plan->price_tzs) }}</span>
                            <span class="text-2xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($plan->price_usd) }}</span>
                            <span class="text-xs text-gray-500">/mo</span>
                        </div>

                        <a href="{{ route('enquiry.create', ['package' => $plan->slug]) }}" class="{{ $plan->is_featured ? 'btn-accent' : 'btn-outline' }} w-full justify-center py-2.5 text-xs mt-4">{{ $plan->is_featured ? 'Subscribe' : 'Choose Plan' }} →</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ============================================================
     TESTIMONIALS — gray, 3-up
============================================================ --}}
<section class="slide py-14 bg-gray-50" id="testimonials">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">What Clients Say</span>
            <h2 class="text-2xl md:text-3xl font-extrabold text-gray-900 mb-2 uppercase tracking-tight">Trusted by Businesses</h2>
            <p class="text-gray-500 text-sm">Hear from the businesses we've helped transform.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition">
                <div class="flex items-center gap-1 mb-3">@for($i = 0; $i < 5; $i++)<svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-gray-700 text-xs leading-relaxed mb-4">"Oweru transformed our online ordering system completely. Our sales increased by 40% in the first month after launch. Professional, responsive, and truly understand business needs."</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold text-xs ring-2 ring-yellow-200">J</div>
                    <div><p class="font-semibold text-gray-900 text-xs">James Mwangi</p><p class="text-gray-500 text-xs">CEO, SeafoodExpress</p></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition">
                <div class="flex items-center gap-1 mb-3">@for($i = 0; $i < 5; $i++)<svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-gray-700 text-xs leading-relaxed mb-4">"As a solo consultant, I needed a professional website that reflected my expertise. The team delivered beyond expectations - modern, fast, and exactly on timeline."</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold text-xs ring-2 ring-yellow-200">S</div>
                    <div><p class="font-semibold text-gray-900 text-xs">Sarah Kavishe</p><p class="text-gray-500 text-xs">Founder, Kavishe Consulting</p></div>
                </div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md transition">
                <div class="flex items-center gap-1 mb-3">@for($i = 0; $i < 5; $i++)<svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-gray-700 text-xs leading-relaxed mb-4">"Their CRM solution streamlined our entire sales process. We went from spreadsheets to automated pipeline management in just 3 weeks. Remarkable work."</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold text-xs ring-2 ring-yellow-200">D</div>
                    <div><p class="font-semibold text-gray-900 text-xs">David Kimaro</p><p class="text-gray-500 text-xs">IT Manager, Meridian Bank</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CTA BANNER — bright yellow accent block
============================================================ --}}
<section class="slide relative overflow-hidden" id="cta">
    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 via-yellow-400 to-yellow-500"></div>
    <div class="pointer-events-none absolute -top-16 -left-16 w-64 h-64 bg-white/10 rounded-full blur-2xl"></div>
    <div class="pointer-events-none absolute -bottom-20 -right-10 w-72 h-72 bg-white/10 rounded-full blur-2xl"></div>

    <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 text-center">
        <div class="inline-flex items-center gap-2 bg-black/10 border border-black/20 rounded-full px-4 py-1.5 text-xs font-bold text-black mb-4">
            <span class="w-2 h-2 bg-black rounded-full animate-pulse"></span>
            Free · No commitment · Results in 60 seconds
        </div>
        <h2 class="text-2xl md:text-3xl font-extrabold text-black mb-3">Is Your Website Losing You <span class="underline decoration-black/30 underline-offset-4">Customers?</span></h2>
        <p class="text-black/70 text-sm mb-6 max-w-xl mx-auto">Most business sites quietly leak leads every day. Find out exactly what yours is costing you — before your competitors do.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('scanner.index') }}" class="inline-flex items-center justify-center gap-2 bg-black text-white text-sm font-semibold px-6 py-3 rounded-lg shadow-lg hover:bg-gray-800 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Free Website Check
            </a>
            <a href="{{ route('enquiry.create') }}" class="inline-flex items-center justify-center gap-2 bg-white text-black text-sm font-semibold px-6 py-3 rounded-lg shadow border border-black/10 hover:bg-gray-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Us
            </a>
        </div>
        <p class="text-xs text-black/60 mt-5 flex flex-wrap items-center justify-center gap-x-5 gap-y-1">
            <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> No signup required</span>
            <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Honest scoring, no scare tactics</span>
        </p>
    </div>
</section>

</div>{{-- /slide-deck --}}

{{-- Slide progress dots (desktop) --}}
<nav class="slide-dots" aria-label="Home page sections">
    @foreach([['id' => 'hero', 'label' => 'Home'], ['id' => 'how-it-works', 'label' => 'How It Works'], ['id' => 'services', 'label' => 'Services'], ['id' => 'crm-showcase', 'label' => 'CRM Solution'], ['id' => 'care-plans', 'label' => 'Care Plans'], ['id' => 'testimonials', 'label' => 'Testimonials'], ['id' => 'cta', 'label' => 'Get Started']] as $dot)
        <button type="button" class="slide-dot" data-target="{{ $dot['id'] }}" title="{{ $dot['label'] }}" aria-label="Go to {{ $dot['label'] }}"></button>
    @endforeach
</nav>

@endsection

@push('scripts')
<script>
    // Slide dots: highlight the slide in view + click-to-jump.
    (function () {
        var deck = document.getElementById('slide-deck');
        if (!deck) return;

        var dots = document.querySelectorAll('.slide-dot');
        function activate(id) {
            dots.forEach(function (d) {
                d.classList.toggle('active', d.dataset.target === id);
            });
        }

        if ('IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) activate(entry.target.id);
                });
            }, { root: deck, threshold: 0.5 });

            deck.querySelectorAll('.slide').forEach(function (slide) {
                observer.observe(slide);
            });
        }

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var slide = document.getElementById(dot.dataset.target);
                if (slide) slide.scrollIntoView({ behavior: 'smooth' });
            });
        });
    })();
</script>
@endpush
