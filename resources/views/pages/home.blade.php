@extends('layouts.public')

@section('title', 'Oweru Tech Solutions - Professional Digital Services')

@section('content')

<section class="relative min-h-[calc(100vh-4rem)] flex items-center bg-black text-white overflow-hidden">
    <!-- Full-bleed background carousel -->
    <div id="hero-carousel" data-carousel data-interval="6000" aria-roledescription="carousel" aria-label="Showcase highlights" class="group absolute inset-0">
        @php
            $slides = $heroSlides->isNotEmpty() ? $heroSlides : collect([
                (object) ['image_path' => 'images/hero/scanner-analytics.jpg', 'title' => 'Website Health Scanner — real diagnostics, instant score', 'subtitle' => null, 'alt_text' => 'Analytics charts on a laptop screen showing a website performance report'],
                (object) ['image_path' => 'images/hero/developer-coding.jpg', 'title' => 'Custom Software — designed, built and shipped by us', 'subtitle' => null, 'alt_text' => 'Developer writing code on a laptop in an office'],
                (object) ['image_path' => 'images/hero/team-collaboration.jpg', 'title' => 'The Oweru Team — real people, real results', 'subtitle' => null, 'alt_text' => 'Our team collaborating around a table in the office'],
            ]);
        @endphp
        @foreach($slides as $slide)
            <figure data-hero-slide class="absolute inset-0 opacity-0 transition-opacity duration-1000 ease-in-out [&.is-active]:opacity-100 {{ $loop->first ? 'is-active' : '' }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                <img src="{{ asset($slide['image_path']) }}" alt="{{ $slide['alt_text'] }}" class="h-full w-full object-cover transition-transform duration-[8000ms] ease-out [.is-active_&]:scale-105" loading="eager" />
                <figcaption class="pointer-events-none absolute inset-x-0 bottom-16 bg-gradient-to-t from-black/55 to-transparent py-6">
                    <span class="mx-auto block max-w-7xl px-4 text-sm font-medium text-yellow-300 sm:px-6 lg:px-8 text-on-photo">{{ $slide['title'] }}</span>
                </figcaption>
            </figure>
        @endforeach

        <div class="pointer-events-none absolute inset-0 bg-black/45"></div>
        <div class="pointer-events-none absolute inset-0 bg-gradient-to-r from-black/75 via-black/40 to-black/10"></div>

        <button type="button" data-hero-prev aria-label="Previous slide" class="absolute left-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white opacity-60 backdrop-blur transition hover:bg-yellow-500 hover:text-black focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 md:opacity-0 md:group-hover:opacity-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg>
        </button>
        <button type="button" data-hero-next aria-label="Next slide" class="absolute right-3 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full bg-black/40 text-white opacity-60 backdrop-blur transition hover:bg-yellow-500 hover:text-black focus:opacity-100 focus:outline-none focus:ring-2 focus:ring-yellow-400 md:opacity-0 md:group-hover:opacity-100">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
        </button>

        <div class="absolute bottom-5 left-1/2 z-10 flex -translate-x-1/2 gap-2" role="tablist" aria-label="Choose slide">
            @foreach($slides as $i => $slide)
                <button type="button" data-hero-dot role="tab" aria-label="Go to slide {{ $loop->iteration }}" class="h-2.5 w-2.5 rounded-full bg-white/40 transition-all duration-300 hover:bg-white/70 [&.is-active]:w-6 [&.is-active]:bg-yellow-400"></button>
            @endforeach
        </div>
    </div>

    <!-- Overlay copy -->
    <div class="relative z-10 w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center lg:text-left max-w-2xl lg:mx-0 text-on-photo">
            <div class="inline-flex items-center gap-2 bg-yellow-500/10 border border-yellow-500/30 rounded-full px-4 py-1.5 text-sm font-medium text-yellow-300 mb-6">
                <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                Trusted by 50+ Businesses Across East Africa
            </div>
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-extrabold mb-6 leading-tight">
                Digital Solutions That
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-yellow-300 to-yellow-500"> Drive Growth</span>
            </h1>
            <p class="text-base text-gray-300 mb-6 leading-relaxed max-w-2xl mx-auto">
                From custom software to complete digital transformation. Oweru delivers measurable
                technical solutions tailored for individuals, SMEs, and corporations across Tanzania and beyond.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ route('scanner.index') }}" class="btn-accent text-sm px-6 py-3 shadow-lg shadow-yellow-500/30">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Free Website Check
                </a>
                <a href="{{ route('packages.index') }}" class="btn-outline border-gray-300 text-white hover:bg-white text-sm px-6 py-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Services & Pricing
                </a>
            </div>
                <div class="mt-6 flex flex-wrap items-center justify-center lg:justify-start gap-4 text-xs text-gray-400">
                    <span class="flex items-center gap-1"><svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Free Consultation</span>
                    <span class="flex items-center gap-1"><svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> No Hidden Fees</span>
                    <span class="flex items-center gap-1"><svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> 24/7 Support</span>
                </div>
            </div>
    </div>
</section>

<section class="bg-white border-b border-black">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="group hover:bg-gray-50 p-2 rounded-lg transition">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-yellow-200"><svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg></div>
                <div class="text-2xl font-extrabold text-black counter" data-target="100">0</div>
                <div class="text-xs text-gray-500">Projects</div>
            </div>
            <div class="group hover:bg-gray-50 p-2 rounded-lg transition">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-yellow-200"><svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
                <div class="text-2xl font-extrabold text-black counter" data-target="50">0</div>
                <div class="text-xs text-gray-500">Businesses</div>
            </div>
            <div class="group hover:bg-gray-50 p-2 rounded-lg transition">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-yellow-200"><svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>
                <div class="text-2xl font-extrabold text-black">99.9%</div>
                <div class="text-xs text-gray-500">Uptime</div>
            </div>
            <div class="group hover:bg-gray-50 p-2 rounded-lg transition">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-2 group-hover:bg-yellow-200"><svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></div>
                <div class="text-2xl font-extrabold text-black">24/7</div>
                <div class="text-xs text-gray-500">Support</div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 lg:py-16 bg-white" id="services">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">Our Services</span>
            <h2 class="text-2xl md:text-3xl font-bold text-black mb-2">Services Tailored to <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-600 to-yellow-400">Your Business</span></h2>
            <p class="text-gray-600 text-sm max-w-xl mx-auto">From simple websites to complex enterprise systems.</p>
            <div class="flex items-center justify-center gap-2 mt-4">
                <span class="text-xs font-medium {{ !$currency || $currency === 'TZS' ? 'text-yellow-600' : 'text-gray-400' }}">TZS</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="currency-toggle" class="sr-only peer" {{ $currency === 'USD' ? 'checked' : '' }}>
                    <div class="w-8 h-4 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-3 after:w-3 after:transition-all peer-checked:bg-yellow-600"></div>
                </label>
                <span class="text-xs font-medium {{ $currency === 'USD' ? 'text-yellow-600' : 'text-gray-400' }}">USD</span>
            </div>
        </div>

        @php
            $serviceCategories = [
                ['key' => 'individuals', 'label' => 'Individuals & Professionals', 'subtitle' => 'Perfect for freelancers, consultants, and professionals building their online presence', 'color' => 'yellow', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'num' => 1],
                ['key' => 'sme', 'label' => 'Small & Medium Enterprises', 'subtitle' => 'For growing businesses that need professional digital infrastructure and support', 'color' => 'yellow', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'num' => 2],
                ['key' => 'corporate', 'label' => 'Business & Corporate', 'subtitle' => 'Enterprise-grade solutions for established businesses and corporate organizations', 'color' => 'yellow', 'icon' => 'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4', 'num' => 3],
            ];
        @endphp

        @foreach($serviceCategories as $category)
            @php
                $packages = match($category['key']) {
                    'individuals' => $individualPackages ?? collect(),
                    'sme' => $smePackages ?? collect(),
                    'corporate' => $corporatePackages ?? collect(),
                };
            @endphp

            <div class="mb-12">
                <div class="flex items-center gap-3 mb-2">
                    <span class="flex-shrink-0 w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $category['icon'] }}"/></svg>
                    </span>
                    <div>
                        <span class="text-xs font-medium text-yellow-600">Category {{ $category['num'] }}</span>
                        <h3 class="text-lg font-bold text-black mt-0.5">{{ $category['label'] }}</h3>
                    </div>
                </div>
                <p class="text-gray-500 ml-13 mt-1 mb-4 text-sm">{{ $category['subtitle'] }}</p>

                @if($packages->isNotEmpty())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($packages as $package)
                            <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 border border-gray-200 hover:border-yellow-300 overflow-hidden">
                                @if($package->is_featured)
                                    <div class="absolute -top-2 left-1/2 -translate-x-1/2 z-10">
                                        <span class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white text-xs font-bold px-3 py-0.5 rounded-full shadow inline-flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                            Recommended
                                        </span>
                                    </div>
                                @endif
                                <div class="p-4">
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        </div>
                                        <span class="badge badge-success text-xs">{{ $package->group_label ?? ucfirst($category['key']) }}</span>
                                    </div>
                                    <h4 class="text-base font-bold text-black mb-2">{{ $package->name }}</h4>
                                    <p class="text-gray-600 text-xs mb-3 leading-relaxed">{{ $package->description }}</p>
                                    <div class="bg-gray-50 rounded-lg p-3 mb-3">
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-xl font-extrabold text-black price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'block' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                            <span class="text-xl font-extrabold text-black price-usd" style="display: {{ $currency === 'USD' ? 'block' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                                            <svg class="w-3 h-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            {{ $package->delivery_days }} day delivery
                                        </div>
                                    </div>
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="{{ $package->is_featured ? 'btn-accent' : 'btn-primary' }} w-full justify-center py-2 text-xs">Get Started →</a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="hidden"></div>
                @endif
            </div>
        @endforeach
    </div>
</section>

<section class="py-8 bg-gray-50 border-y border-black/10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-sm text-gray-500 uppercase tracking-widest mb-8">Trusted by leading businesses across East Africa</p>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-16 opacity-60">
            @php
                $clientLogos = [
                    ['name' => 'MTN', 'color' => 'text-yellow-600'],
                    ['name' => 'CRDB', 'color' => 'text-yellow-600'],
                    ['name' => 'NMB', 'color' => 'text-yellow-600'],
                    ['name' => 'Azam TV', 'color' => 'text-yellow-600'],
                    ['name' => 'TIC', 'color' => 'text-yellow-600'],
                    ['name' => 'EABC', 'color' => 'text-yellow-600'],
                ];
            @endphp
            @foreach($clientLogos as $logo)
                <div class="text-lg font-bold {{ $logo['color'] }} hover:opacity-100 transition cursor-default">{{ $logo['name'] }}</div>
            @endforeach
        </div>
    </div>
</section>

<section class="py-10 lg:py-14 bg-black text-white" id="crm-showcase">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div>
                <span class="inline-block px-3 py-1 bg-yellow-500/20 text-yellow-300 text-xs font-semibold rounded-full mb-3">CRM Solution</span>
                <h2 class="text-2xl md:text-3xl font-bold mb-3 leading-tight">Take Control of Your <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 to-yellow-300">Customer Relationships</span></h2>
                <p class="text-gray-300 text-base mb-4">Stop losing leads. Our custom CRM tracks every interaction, automates follow-ups, and grows your sales pipeline.</p>
                <div class="space-y-3 mb-4">
                    <div class="flex items-start gap-2">
                        <div class="w-5 h-5 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-2.5 h-2.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                        <div><p class="font-semibold text-white text-sm">Track Every Lead</p><p class="text-gray-400 text-xs">All leads captured in one place.</p></div>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="w-5 h-5 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-2.5 h-2.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                        <div><p class="font-semibold text-white text-sm">Automate Follow-ups</p><p class="text-gray-400 text-xs">Set it and forget it.</p></div>
                    </div>
                    <div class="flex items-start gap-2">
                        <div class="w-5 h-5 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"><svg class="w-2.5 h-2.5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></div>
                        <div><p class="font-semibold text-white text-sm">Detailed Analytics</p><p class="text-gray-400 text-xs">Insights for better decisions.</p></div>
                    </div>
                </div>
                <a href="{{ route('enquiry.create', ['package' => 'digital-transformation']) }}" class="btn-accent text-sm">Get CRM Solution →</a>
            </div>
            <div class="relative">
                <div class="bg-gray-900 rounded-xl shadow-2xl p-4 border border-yellow-500/20">
                    <div class="flex items-center gap-2 mb-4 pb-3 border-b border-gray-700">
                        <div class="w-2.5 h-2.5 bg-yellow-500 rounded-full"></div>
                        <div class="w-2.5 h-2.5 bg-yellow-600 rounded-full"></div>
                        <div class="w-2.5 h-2.5 bg-yellow-400 rounded-full"></div>
                        <span class="text-gray-400 text-xs ml-1">CRM Dashboard</span>
                    </div>
                    <div class="mb-4">
                        <h4 class="text-xs text-gray-400 uppercase tracking-wider mb-2">Sales Pipeline</h4>
                        <div class="space-y-2">
                            <div class="flex items-center gap-2"><span class="text-xs text-gray-400 w-16">New</span><div class="flex-1 h-2.5 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-500 rounded-full" style="width: 100%"></div></div><span class="text-xs font-bold text-yellow-400">24</span></div>
                            <div class="flex items-center gap-2"><span class="text-xs text-gray-400 w-16">Qualified</span><div class="flex-1 h-2.5 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-400 rounded-full" style="width: 75%"></div></div><span class="text-xs font-bold text-yellow-300">18</span></div>
                            <div class="flex items-center gap-2"><span class="text-xs text-gray-400 w-16">Proposal</span><div class="flex-1 h-2.5 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-600 rounded-full" style="width: 50%"></div></div><span class="text-xs font-bold text-yellow-500">12</span></div>
                            <div class="flex items-center gap-2"><span class="text-xs text-gray-400 w-16">Won</span><div class="flex-1 h-2.5 bg-gray-700 rounded-full overflow-hidden"><div class="h-full bg-yellow-300 rounded-full" style="width: 30%"></div></div><span class="text-xs font-bold text-yellow-200">7</span></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3 mt-4 pt-3 border-t border-gray-700">
                        <div class="text-center"><p class="text-xl font-bold text-yellow-400">45%</p><p class="text-xs text-gray-400">Conversion</p></div>
                        <div class="text-center"><p class="text-xl font-bold text-yellow-300">156</p><p class="text-xs text-gray-400">Leads</p></div>
                        <div class="text-center"><p class="text-xl font-bold text-yellow-500">8.2K</p><p class="text-xs text-gray-400">Revenue</p></div>
                    </div>
                </div>
                <div class="absolute -bottom-3 -right-3 bg-yellow-500 text-black px-3 py-1.5 rounded-full text-xs font-bold shadow-lg flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    From TZS 1.5M
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-12 bg-white" id="care-plans">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">Ongoing Support</span>
            <h2 class="text-2xl md:text-3xl font-bold text-black mb-3">Monthly Care Plans</h2>
            <p class="text-gray-600 text-base max-w-xl mx-auto">Keep your digital systems running smoothly with our maintenance and support plans.</p>
        </div>

        @if($carePlans->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($carePlans as $plan)
                    <div class="group relative bg-white rounded-xl shadow-sm hover:shadow-lg transition-all duration-200 border border-gray-200 hover:border-yellow-300 {{ $plan->is_featured ? 'ring-2 ring-yellow-500 ring-offset-2' : '' }}">
                        @if($plan->is_featured)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 z-10">
                                <span class="bg-gradient-to-r from-yellow-500 to-yellow-400 text-black text-xs font-bold px-3 py-1 rounded-full shadow inline-flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Best Value
                            </span>
                            </div>
                        @endif
                        <div class="p-4 pt-6">
                            <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <h3 class="text-base font-bold text-black mb-1">{{ $plan->name }}</h3>
                            <p class="text-gray-600 text-xs mb-3">{{ $plan->description }}</p>
                            <div class="bg-gray-50 rounded-lg p-3 mb-3 text-center">
                                <div class="flex items-baseline justify-center gap-1">
                                    <span class="text-2xl font-extrabold text-black price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'block' : 'none' }}">TZS {{ number_format($plan->price_tzs) }}</span>
                                    <span class="text-2xl font-extrabold text-black price-usd" style="display: {{ $currency === 'USD' ? 'block' : 'none' }}">${{ number_format($plan->price_usd) }}</span>
                                    <span class="text-xs text-gray-500">/mo</span>
                                </div>
                            </div>
                            <a href="{{ route('enquiry.create', ['package' => $plan->slug]) }}" class="{{ $plan->is_featured ? 'btn-accent' : 'btn-outline' }} w-full justify-center py-2 text-xs">{{ $plan->is_featured ? 'Subscribe' : 'Choose Plan' }} →</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="hidden"></div>
        @endif
    </div>
</section>

<section class="py-10 bg-gray-50" id="exclusions">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="bg-gradient-to-r from-gray-100 to-gray-50 p-4">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg></div>
                        <div><h3 class="text-base font-bold text-black">Shared Exclusions</h3><p class="text-xs text-gray-500">Not in standard packages</p></div>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-3">Not included in any standard package. Custom pricing available.</p>
                    <ul class="space-y-2">
                        @forelse($exclusions as $exclusion)
                            <li class="flex items-start gap-2 p-2 bg-gray-50 rounded text-sm"><span class="w-5 h-5 bg-black rounded-full flex items-center justify-center flex-shrink-0"><svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg></span><span>{{ $exclusion->description }}</span></li>
                        @empty
                            <li class="text-xs text-gray-400 hidden">No exclusions configured.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 p-4">
                    <div class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg></div>
                        <div><h3 class="text-base font-bold text-black">Our Commitments</h3><p class="text-xs text-gray-500">What to expect</p></div>
                    </div>
                </div>
                <div class="p-4">
                    <p class="text-xs text-gray-500 mb-3">Every engagement comes with these guarantees.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @forelse($commitments as $commitment)
                            <div class="flex items-start gap-2 p-2 bg-yellow-50 rounded text-sm"><span class="w-5 h-5 bg-yellow-400 rounded-full flex items-center justify-center flex-shrink-0"><svg class="w-3 h-3 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg></span><div><p class="font-medium text-black text-xs">{{ $commitment->title }}</p><p class="text-gray-500 text-xs mt-0.5">{{ $commitment->description }}</p></div></div>
                        @empty
                            <div class="text-xs text-gray-400 col-span-2 hidden">No commitments configured.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-10 bg-white" id="testimonials">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">What Clients Say</span>
            <h2 class="text-2xl font-bold text-black mb-2">Trusted by Businesses</h2>
            <p class="text-gray-600 text-sm">Hear from the businesses we've helped transform.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                <div class="flex items-center gap-1 mb-2">@for($i = 0; $i < 5; $i++)<svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-gray-700 text-xs leading-relaxed mb-3">"Oweru transformed our online ordering system completely. Our sales increased by 40% in the first month after launch. Professional, responsive, and truly understand business needs."</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold text-xs">J</div>
                    <div><p class="font-semibold text-black text-xs">James Mwangi</p><p class="text-gray-500 text-xs">CEO, SeafoodExpress</p></div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                <div class="flex items-center gap-1 mb-2">@for($i = 0; $i < 5; $i++)<svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-gray-700 text-xs leading-relaxed mb-3">"As a solo consultant, I needed a professional website that reflected my expertise. The team delivered beyond expectations - modern, fast, and exactly on timeline."</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold text-xs">S</div>
                    <div><p class="font-semibold text-black text-xs">Sarah Kavishe</p><p class="text-gray-500 text-xs">Founder, Kavishe Consulting</p></div>
                </div>
            </div>
            <div class="bg-gray-50 rounded-xl p-4 hover:bg-gray-100 transition">
                <div class="flex items-center gap-1 mb-2">@for($i = 0; $i < 5; $i++)<svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-gray-700 text-xs leading-relaxed mb-3">"Their CRM solution streamlined our entire sales process. We went from spreadsheets to automated pipeline management in just 3 weeks. Remarkable work."</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-black font-bold text-xs">D</div>
                    <div><p class="font-semibold text-black text-xs">David Kimaro</p><p class="text-gray-500 text-xs">IT Manager, Meridian Bank</p></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="py-10 bg-black text-white relative overflow-hidden">
    <div class="absolute top-0 right-0 w-96 h-96 bg-yellow-500/5 rounded-full blur-3xl"></div>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
        <div class="inline-flex items-center gap-2 bg-yellow-500/10 border border-yellow-500/30 rounded-full px-4 py-1.5 text-xs font-medium text-yellow-300 mb-4">
            <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
            Free · No commitment · Results in 60 seconds
        </div>
        <h2 class="text-2xl md:text-3xl font-bold mb-3">Is Your Website Losing You <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-400 via-yellow-300 to-yellow-500">Customers?</span></h2>
        <p class="text-gray-300 text-sm mb-5 max-w-xl mx-auto">Most business sites quietly leak leads every day. Find out exactly what yours is costing you — before your competitors do.</p>
        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('scanner.index') }}" class="btn-accent text-sm px-6 py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Free Website Check
            </a>
            <a href="{{ route('enquiry.create') }}" class="btn-outline text-sm px-6 py-2.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Contact Us
            </a>
        </div>
        <p class="text-xs text-gray-500 mt-4 flex items-center justify-center gap-4">
            <span class="flex items-center gap-1"><svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg> No signup required</span>
            <span class="flex items-center gap-1"><svg class="w-3 h-3 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Honest scoring, no scare tactics</span>
        </p>
    </div>
</section>

<section class="py-12 bg-white border-t border-black/10" id="how-it-works">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full mb-3">How It Works</span>
            <h2 class="text-2xl md:text-3xl font-bold text-black">From First Scan to Working With Us</h2>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
            <div class="hidden md:block absolute top-6 left-[12%] right-[12%] h-0.5 bg-gradient-to-r from-yellow-200 via-yellow-400 to-yellow-200"></div>
            @foreach([
                ['icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'title' => 'Scan Your Site', 'desc' => 'Run the free check — 23 automated checks across 8 areas score your site out of 100.'],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'title' => 'Get Your Scorecard', 'desc' => 'See every finding, what it means for your business, and the exact fix it needs.'],
                ['icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'title' => 'Talk It Through', 'desc' => 'A free consultation maps the fixes to your goals and budget — zero pressure.'],
                ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Watch It Grow', 'desc' => 'We implement the improvements and you track the score climb on every re-scan.'],
            ] as $step)
                <div class="text-center relative">
                    <div class="w-12 h-12 mx-auto mb-3 bg-yellow-500 rounded-xl flex items-center justify-center shadow-lg shadow-yellow-500/30 ring-4 ring-white">
                        <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/></svg>
                    </div>
                    <h3 class="font-bold text-black text-sm mb-1">{{ $loop->iteration }}. {{ $step['title'] }}</h3>
                    <p class="text-gray-500 text-xs leading-relaxed">{{ $step['desc'] }}</p>
                </div>
            @endforeach
        </div>
        <div class="text-center mt-8">
            <a href="{{ route('scanner.index') }}" class="btn-accent text-sm">
                Start With Step 1 — It's Free
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>

@endsection
