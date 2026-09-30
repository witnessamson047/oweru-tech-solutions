@extends('layouts.public')

@section('title', 'Oweru Tech Solutions - Professional Digital Services')

@section('content')

{{-- ============================================================
     HERO — full-bleed photo, calm overlay, one clear message
============================================================ --}}
<section class="relative overflow-hidden bg-gray-950" id="hero">

    @php
        $heroSlides = [
            ['src' => asset('images/services/header-consultation.jpg'), 'alt' => 'Oweru team reviewing a client project together'],
            ['src' => asset('images/hero/team-collaboration.jpg'), 'alt' => 'Oweru team collaborating around a table'],
            ['src' => asset('images/hero/developer-coding.jpg'), 'alt' => 'Oweru developer writing code on a laptop'],
        ];
    @endphp

    {{-- Slow cross-fading photo background --}}
    <div data-carousel data-interval="6000" aria-roledescription="carousel" aria-label="Examples of our work" class="absolute inset-0">
        @foreach($heroSlides as $slide)
            <figure data-hero-slide class="absolute inset-0 opacity-0 transition-opacity duration-[1200ms] ease-in-out [&.is-active]:opacity-100 {{ $loop->first ? 'is-active' : '' }}" aria-hidden="{{ $loop->first ? 'false' : 'true' }}">
                <img src="{{ $slide['src'] }}" alt="{{ $slide['alt'] }}" class="h-full w-full object-cover" />
            </figure>
        @endforeach
    </div>

    {{-- Readability overlay — dark enough for contrast, photos still visible --}}
    <div class="absolute inset-0 bg-gray-950/70"></div>

    <div class="relative max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-24 sm:py-32 lg:py-40 text-center">
        <span class="badge-gold mb-6">{{ __('home.hero.badge') }}</span>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-tight text-white text-on-photo leading-[1.1] max-w-3xl mx-auto">
            {{ __('home.hero.title_1') }}
            <span class="text-yellow-400">{{ __('home.hero.title_2') }}</span>
        </h1>

        <p class="mt-6 text-base sm:text-lg text-gray-200 text-on-photo leading-relaxed max-w-2xl mx-auto">
            {{ __('home.hero.lead') }}
        </p>

        <div class="mt-10 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('scanner.index') }}" class="btn-accent text-base px-6 py-3.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                {{ __('home.hero.free_check') }}
            </a>
            <a href="{{ route('packages.index') }}" class="btn-outline-light text-base px-6 py-3.5">
                {{ __('home.hero.services_pricing') }}
            </a>
        </div>

        <p class="mt-8 text-xs sm:text-sm text-gray-300 text-on-photo flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
            <span>{{ __('home.hero.free_consultation') }}</span>
            <span class="hidden sm:inline text-gray-500" aria-hidden="true">·</span>
            <span>{{ __('home.hero.no_hidden_fees') }}</span>
            <span class="hidden sm:inline text-gray-500" aria-hidden="true">·</span>
            <span>{{ __('home.hero.support_247') }}</span>
        </p>
    </div>
</section>

{{-- ============================================================
     TRUST STRIP — quiet stats on white
============================================================ --}}
<section class="border-b border-gray-100 bg-white" aria-label="Company statistics">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
        <dl class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 text-center">
            @foreach([
                ['value' => '100+', 'label' => __('home.stats.projects')],
                ['value' => '50+', 'label' => __('home.stats.businesses')],
                ['value' => '99.9%', 'label' => __('home.stats.uptime')],
                ['value' => '24/7', 'label' => __('home.stats.support')],
            ] as $stat)
                <div>
                    <dt class="sr-only">{{ $stat['label'] }}</dt>
                    <dd class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $stat['value'] }}</dd>
                    <dd class="mt-1 text-xs sm:text-sm text-gray-500">{{ $stat['label'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- ============================================================
     HOW IT WORKS — four steps, no decoration
============================================================ --}}
<section class="py-16 sm:py-20 lg:py-24 bg-white" id="how-it-works">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-12 sm:mb-16">
            <span class="badge-gold mb-4">{{ __('home.how.badge') }}</span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('home.how.title') }}</h2>
        </div>

        <ol class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-10">
            @foreach([
                ['icon' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z', 'title' => __('home.how.scan.title'), 'desc' => __('home.how.scan.desc')],
                ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'title' => __('home.how.scorecard.title'), 'desc' => __('home.how.scorecard.desc')],
                ['icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'title' => __('home.how.talk.title'), 'desc' => __('home.how.talk.desc')],
                ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => __('home.how.grow.title'), 'desc' => __('home.how.grow.desc')],
            ] as $step)
                <li class="text-center sm:text-left">
                    <div class="flex sm:flex-col items-center sm:items-start gap-4 sm:gap-0">
                        <span class="w-11 h-11 rounded-xl bg-yellow-50 text-yellow-700 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $step['icon'] }}"/></svg>
                        </span>
                        <span class="sm:hidden text-sm font-bold text-yellow-700">{{ $loop->iteration }}</span>
                        <span class="hidden sm:block mt-4 mb-2 text-sm font-bold text-yellow-700">{{ __('home.how.step') }} {{ $loop->iteration }}</span>
                    </div>
                    <h3 class="mt-3 text-base font-semibold text-gray-900">{{ $step['title'] }}</h3>
                    <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">{{ $step['desc'] }}</p>
                </li>
            @endforeach
        </ol>

        <div class="text-center mt-12">
            <a href="{{ route('scanner.index') }}" class="btn-accent">
                {{ __('home.how.cta') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     SERVICES — three featured packages, quiet cards
============================================================ --}}
<section class="py-16 sm:py-20 lg:py-24 bg-gray-50 border-y border-gray-100" id="services">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-12 sm:mb-14">
            <span class="badge-gold mb-4">{{ __('home.services.badge') }}</span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('home.services.title') }}</h2>
            <p class="mt-3 text-sm sm:text-base text-gray-600">{{ __('home.services.lead') }}</p>

            {{-- Currency toggle --}}
            <div class="mt-6 inline-flex items-center gap-3">
                <span class="text-sm font-medium {{ !$currency || $currency === 'TZS' ? 'text-gray-900' : 'text-gray-400' }}">TZS</span>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="currency-toggle" class="sr-only peer" {{ $currency === 'USD' ? 'checked' : '' }} aria-label="Show prices in USD">
                    <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-gray-900 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
                </label>
                <span class="text-sm font-medium {{ $currency === 'USD' ? 'text-gray-900' : 'text-gray-400' }}">USD</span>
            </div>
        </div>

        @php
            $packageImages = [
                'software-development' => ['image' => 'images/services/software-development.jpg', 'alt' => 'Developer writing code across two monitors'],
                'mobile-web-development' => ['image' => 'images/services/mobile-web-development.jpg', 'alt' => 'Responsive website design displayed on laptop and phone'],
                'crm-solutions' => ['image' => 'images/services/crm-solutions.jpg', 'alt' => 'Sales dashboard with charts on a computer screen'],
                'network-design' => ['image' => 'images/services/network-design.jpg', 'alt' => 'Technician configuring network equipment'],
            ];
            $defaultPackageImage = ['image' => 'images/hero/team-collaboration.jpg', 'alt' => 'Oweru team collaborating around a table'];
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($highlightedPackages as $package)
                @php
                    $meta = $packageImages[$package->slug] ?? $defaultPackageImage;
                    $categoryLabel = match($package->group) {
                        'individuals' => __('home.services.individuals'),
                        'sme' => __('home.services.sme'),
                        'corporate' => __('home.services.corporate'),
                        default => ucfirst($package->group ?? 'All'),
                    };
                @endphp
                <article class="flex flex-col bg-white rounded-2xl border border-gray-200 overflow-hidden transition-shadow hover:shadow-lg">
                    <img src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="w-full h-40 sm:h-44 object-cover" loading="lazy">
                    <div class="flex flex-col flex-1 p-6">
                        <span class="text-xs font-medium text-yellow-700 uppercase tracking-wide">{{ $categoryLabel }}</span>
                        <h3 class="mt-1.5 text-lg font-semibold text-gray-900">{{ $package->name }}</h3>
                        <p class="mt-2 text-sm text-gray-600 leading-relaxed flex-1">{{ $package->description }}</p>
                        <div class="mt-5 flex items-baseline gap-2">
                            <span class="text-xl font-bold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                            <span class="text-xl font-bold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                            <span class="text-xs text-gray-500">· {{ $package->delivery_days }} {{ __('home.services.day_delivery') }}</span>
                        </div>
                        <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="{{ $package->is_featured ? 'btn-accent' : 'btn-secondary' }} w-full mt-5 py-2.5 text-sm">{{ __('site.common.get_started') }}</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="text-center mt-10">
            <a href="{{ route('packages.index') }}" class="btn-secondary">
                {{ __('site.common.view_all_services') }}
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     CRM SHOWCASE — split layout, photo right
============================================================ --}}
<section class="py-16 sm:py-20 lg:py-24 bg-white" id="crm-showcase">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div class="order-2 lg:order-1">
                <span class="badge-gold mb-4">{{ __('home.crm.badge') }}</span>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('home.crm.title') }}</h2>
                <p class="mt-4 text-base text-gray-600 leading-relaxed">{{ __('home.crm.lead') }}</p>

                <ul class="mt-8 space-y-5">
                    @foreach([
                        ['title' => __('home.crm.leads_title'), 'desc' => __('home.crm.leads_desc')],
                        ['title' => __('home.crm.followups_title'), 'desc' => __('home.crm.followups_desc')],
                        ['title' => __('home.crm.analytics_title'), 'desc' => __('home.crm.analytics_desc')],
                    ] as $item)
                        <li class="flex items-start gap-3.5">
                            <span class="w-6 h-6 rounded-full bg-yellow-50 text-yellow-700 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $item['title'] }}</p>
                                <p class="text-gray-600 text-sm mt-0.5">{{ $item['desc'] }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('enquiry.create', ['package' => 'digital-transformation']) }}" class="btn-accent mt-8">
                    {{ __('home.crm.cta') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <div class="order-1 lg:order-2">
                <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                    <img src="{{ asset('images/services/crm-solutions.jpg') }}" alt="Sales analytics dashboard on a monitor" class="w-full h-64 sm:h-80 lg:h-[26rem] object-cover" loading="lazy">
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================================================
     CARE PLANS — three quiet cards
============================================================ --}}
<section class="py-16 sm:py-20 lg:py-24 bg-gray-50 border-y border-gray-100" id="care-plans">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-12 sm:mb-14">                <span class="badge-gold mb-4">{{ __('home.care.badge') }}</span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('home.care.title') }}</h2>
            <p class="mt-3 text-sm sm:text-base text-gray-600">{{ __('home.care.lead') }}</p>
        </div>

        @if($carePlans->isNotEmpty())
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($carePlans as $plan)
                    @php $isFeatured = $plan->is_featured; @endphp
                    <article class="relative flex flex-col bg-white rounded-2xl border overflow-hidden transition-shadow hover:shadow-lg {{ $isFeatured ? 'border-yellow-500 shadow-md' : 'border-gray-200' }}">
                        @if($isFeatured)
                            <span class="absolute top-4 right-4 bg-yellow-100 text-yellow-900 text-[11px] font-semibold px-2.5 py-1 rounded-full">Best value</span>
                        @endif

                        <div class="p-6 sm:p-8 flex flex-col flex-1">
                            <h3 class="text-lg font-semibold text-gray-900">{{ $plan->name }}</h3>
                            <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $plan->description }}</p>

                            @if($plan->features)
                                <ul class="mt-6 pt-6 border-t border-gray-100 space-y-3 flex-1">
                                    @foreach($plan->features as $feature)
                                        <li class="flex items-start gap-2.5">
                                            <svg class="w-4 h-4 mt-0.5 text-yellow-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-600 leading-relaxed">{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="flex-1"></div>
                            @endif

                            <div class="mt-6 pt-6 border-t border-gray-100 flex items-baseline gap-1">
                                <span class="text-2xl font-bold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($plan->price_tzs) }}</span>
                                <span class="text-2xl font-bold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($plan->price_usd) }}</span>
                                <span class="text-sm text-gray-500">{{ __('site.common.month') }}</span>
                            </div>

                            <a href="{{ route('enquiry.create', ['package' => $plan->slug]) }}" class="{{ $isFeatured ? 'btn-accent' : 'btn-secondary' }} w-full mt-5 py-2.5 text-sm">{{ $isFeatured ? __('home.care.subscribe') : __('home.care.choose') }}</a>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ============================================================
     TESTIMONIALS — three quotes, no clutter
============================================================ --}}
<section class="py-16 sm:py-20 lg:py-24 bg-white" id="testimonials">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-12 sm:mb-14">                <span class="badge-gold mb-4">{{ __('home.testimonials.badge') }}</span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('home.testimonials.title') }}</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                ['quote' => '"Oweru transformed our online ordering system completely. Our sales increased by 40% in the first month after launch. Professional, responsive, and truly understand business needs."', 'name' => 'Pendo Oweru', 'role' => 'CEO, SeafoodExpress'],
                ['quote' => '"As a solo consultant, I needed a professional website that reflected my expertise. The team delivered beyond expectations — modern, fast, and exactly on timeline."', 'name' => 'Pendo Oweru', 'role' => 'Founder, Kavishe Consulting'],
                ['quote' => '"Their CRM solution streamlined our entire sales process. We went from spreadsheets to automated pipeline management in just 3 weeks. Remarkable work."', 'name' => 'Pendo Oweru', 'role' => 'IT Manager, Meridian Bank'],
            ] as $t)
                <figure class="flex flex-col bg-white rounded-2xl border border-gray-200 p-6 sm:p-8">
                    <div class="flex items-center gap-1 text-yellow-500" aria-label="{{ __('home.testimonials.stars') }}">
                        @for($i = 0; $i < 5; $i++)<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                    </div>
                    <blockquote class="mt-4 text-sm text-gray-700 leading-relaxed flex-1">{{ $t['quote'] }}</blockquote>
                    <figcaption class="mt-6 pt-6 border-t border-gray-100 flex items-center gap-3">
                        <span class="w-9 h-9 rounded-full bg-gray-900 text-white flex items-center justify-center text-sm font-semibold">{{ substr($t['name'], 0, 1) }}</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $t['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $t['role'] }}</p>
                        </div>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>

{{-- ============================================================
     CTA — dark, calm, one decision
============================================================ --}}
<section class="bg-gray-950" id="cta">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24 text-center">
        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-bold tracking-tight text-white">{{ __('home.cta.title') }}</h2>
        <p class="mt-4 text-sm sm:text-base text-gray-400 leading-relaxed">{{ __('home.cta.lead') }}</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('scanner.index') }}" class="btn-accent text-base px-6 py-3.5 !bg-yellow-500 !text-gray-950 hover:!bg-yellow-400">
                {{ __('home.cta.run_check') }}
            </a>
            <a href="{{ route('contact.create') }}" class="btn-outline-light text-base px-6 py-3.5">
                {{ __('home.cta.contact') }}
            </a>
        </div>
        <p class="mt-6 text-xs text-gray-500">{{ __('home.cta.note') }}</p>
    </div>
</section>

@endsection
