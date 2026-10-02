@extends('layouts.public')

@section('title', __('packages.header.badge') . ' - Oweru Tech Solutions')

@section('content')

{{-- Hero section --}}
<section class="services-hero">
    <div class="services-hero-inner">
        <div class="services-hero-copy">
            <span class="services-hero-badge">
                {{ __('packages.header.badge') }}
            </span>
            <h1>{{ __('packages.header.title') }}</h1>
            <p>{{ __('packages.header.lead') }}</p>

            <div class="services-hero-actions">
                <a href="{{ route('enquiry.create') }}" class="services-primary-btn">Get a quote</a>
                <a href="#service-package-grid" class="services-secondary-btn">Explore services</a>
            </div>

            <div class="services-hero-stats">
                <div>
                    <strong>6+</strong>
                    <span>core packages</span>
                </div>
                <div>
                    <strong>30+</strong>
                    <span>business solutions</span>
                </div>
                <div>
                    <strong>1:1</strong>
                    <span>consultation</span>
                </div>
            </div>
        </div>

        <div class="services-feature-card">
            <div class="services-feature-top">
                <span>Most popular</span>
                <span class="services-feature-pill">Featured</span>
            </div>

            <div class="services-feature-price">
                <div class="services-price">TZS 1.2M</div>
                <div class="services-name">Professional Portfolio</div>
            </div>

            <ul>
                <li>Multi-page website</li>
                <li>Mobile responsive design</li>
                <li>Contact and enquiry system</li>
            </ul>

            <a href="{{ route('enquiry.create', ['package' => 'professional-portfolio']) }}" class="services-card-btn">Book a consult</a>
        </div>
    </div>

    <div class="services-currency-wrap">
        <div class="services-currency-box">
            <span class="{{ !$currency || $currency === 'TZS' ? 'active' : '' }}">TZS</span>
            <label class="services-toggle">
                <input type="checkbox" id="currency-toggle" {{ $currency === 'USD' ? 'checked' : '' }} aria-label="{{ __('packages.header.show_usd') }}">
                <span class="services-toggle-slider"></span>
            </label>
            <span class="{{ $currency === 'USD' ? 'active' : '' }}">USD</span>
        </div>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        @php
            $serviceLines = \App\Models\ServiceLine::active()->get();
            $uncategorised = $packages->whereNull('service_line_id');
            $packageImages = [
                'digital-transformation' => ['image' => 'images/services/software-development.jpg', 'alt' => 'Developer writing code across two monitors'],
                'starter-website' => ['image' => 'images/services/mobile-web-development.jpg', 'alt' => 'Responsive website displayed on a laptop and phone'],
                'professional-portfolio' => ['image' => 'images/services/header-consultation.jpg', 'alt' => 'Technology team discussing a client project'],
                'business-website' => ['image' => 'images/services/crm-solutions.jpg', 'alt' => 'Business dashboard with customer and sales information'],
                'ecommerce-starter' => ['image' => 'images/hero/scanner-analytics.jpg', 'alt' => 'Website analytics displayed on a laptop'],
                'corporate-platform' => ['image' => 'images/services/network-design.jpg', 'alt' => 'Network equipment configured for a business'],
            ];
        @endphp

        @php
            $allPackageCards = collect();

            foreach ($serviceLines as $line) {
                foreach ($packages->where('service_line_id', $line->id) as $package) {
                    $allPackageCards->push(['package' => $package, 'line' => $line]);
                }
            }

            foreach ($uncategorised as $package) {
                $allPackageCards->push(['package' => $package, 'line' => null]);
            }

            $visiblePackageCards = $allPackageCards->take(6);
            $hiddenPackageCards = $allPackageCards->skip(6);
        @endphp

        @if($visiblePackageCards->isNotEmpty())
            <div class="mb-14">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6" id="service-package-grid">
                    @foreach($visiblePackageCards as $card)
                        @php
                            $package = $card['package'];
                            $meta = $packageImages[$package->slug] ?? null;
                        @endphp
                        <article data-package-card="true" class="service-package-card group relative flex flex-col bg-white shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                            @if($package->is_featured)
                                <span class="absolute top-4 right-4 bg-amber-100 text-amber-800 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-amber-200">{{ __('packages.packages.recommended') }}</span>
                            @endif

                            @if($meta)
                                <img data-package-image="{{ $package->slug }}" src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="service-package-card-image w-full h-32 object-cover bg-slate-100" loading="lazy">
                            @else
                                <div class="service-package-artwork" aria-hidden="true"></div>
                            @endif

                            <div class="service-package-card-content flex flex-col flex-1 p-4 sm:p-5">
                                <h3 class="text-base font-semibold text-slate-900 leading-snug">{{ $package->name }}</h3>
                                <p class="mt-2 text-sm text-slate-600 leading-relaxed flex-1 line-clamp-3">{{ $package->description }}</p>

                                <div class="mt-4 flex items-baseline gap-2 border-t border-slate-100 pt-3">
                                    <span class="text-lg font-bold text-slate-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                    <span class="text-lg font-bold text-slate-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500">{{ __('packages.packages.delivery_days', ['days' => $package->delivery_days]) }}</p>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <a href="{{ route('packages.show', ['package' => $package->slug]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 transition-colors">View details</a>
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="service-enquire-btn inline-flex items-center justify-center rounded-xl px-2.5 py-2 text-xs transition-colors">{{ __('packages.packages.enquire') }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach

                    @foreach($hiddenPackageCards as $card)
                        @php
                            $package = $card['package'];
                            $meta = $packageImages[$package->slug] ?? null;
                        @endphp
                        <article data-package-card="false" class="service-package-card relative hidden service-hidden-card flex flex-col bg-white shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden">
                            @if($package->is_featured)
                                <span class="absolute top-4 right-4 bg-amber-100 text-amber-800 text-[11px] font-semibold px-2.5 py-1 rounded-full border border-amber-200">{{ __('packages.packages.recommended') }}</span>
                            @endif

                            @if($meta)
                                <img data-package-image="{{ $package->slug }}" src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="service-package-card-image w-full h-32 object-cover bg-slate-100" loading="lazy">
                            @else
                                <div class="service-package-artwork" aria-hidden="true"></div>
                            @endif

                            <div class="service-package-card-content flex flex-col flex-1 p-4 sm:p-5">
                                <h3 class="text-base font-semibold text-slate-900 leading-snug">{{ $package->name }}</h3>
                                <p class="mt-2 text-sm text-slate-600 leading-relaxed flex-1 line-clamp-3">{{ $package->description }}</p>

                                <div class="mt-4 flex items-baseline gap-2 border-t border-slate-100 pt-3">
                                    <span class="text-lg font-bold text-slate-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                    <span class="text-lg font-bold text-slate-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                </div>
                                <p class="mt-1 text-[11px] text-slate-500">{{ __('packages.packages.delivery_days', ['days' => $package->delivery_days]) }}</p>

                                <div class="mt-4 grid grid-cols-2 gap-2">
                                    <a href="{{ route('packages.show', ['package' => $package->slug]) }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-slate-50 px-2.5 py-2 text-xs font-medium text-slate-700 hover:bg-slate-100 transition-colors">View details</a>
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="service-enquire-btn inline-flex items-center justify-center rounded-xl px-2.5 py-2 text-xs transition-colors">{{ __('packages.packages.enquire') }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>

                @if($hiddenPackageCards->isNotEmpty())
                    <div class="mt-6 text-center">
                        <button type="button" class="btn-accent px-6 py-3 text-sm font-medium" data-toggle-packages data-target="service-package-grid" aria-expanded="false">View more</button>
                    </div>
                @endif
            </div>
        @endif

        @if($uncategorised->isNotEmpty())
            <div class="mb-14">
                <div class="mb-6 pb-5 border-b border-gray-100">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">{{ __('packages.packages.other') }}</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($uncategorised as $package)
                        @php $meta = $packageImages[$package->slug] ?? null; @endphp
                        <article class="service-package-card service-package-card-uncategorized relative flex flex-col bg-white shadow-sm overflow-hidden">
                            @if($package->is_featured)
                                <span class="absolute top-4 right-4 bg-yellow-100 text-yellow-900 text-[11px] font-semibold px-2.5 py-1 rounded-full">{{ __('packages.packages.recommended') }}</span>
                            @endif

                            @if($meta)
                                <img data-package-image="{{ $package->slug }}" src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="service-package-card-image w-full h-40 object-cover" loading="lazy">
                            @else
                                <div class="service-package-artwork service-package-artwork-tall" aria-hidden="true"></div>
                            @endif

                            <div class="service-package-card-content flex flex-col flex-1 p-6">
                                <h3 class="text-base font-semibold text-gray-900">{{ $package->name }}</h3>
                                <p class="mt-2 text-sm text-gray-600 leading-relaxed flex-1">{{ $package->description }}</p>

                                <div class="mt-5 flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                    <span class="text-xl font-bold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ __('packages.packages.delivery_days', ['days' => $package->delivery_days]) }}</p>

                                <div class="mt-5 flex items-center gap-2">
                                    <a href="{{ route('packages.show', ['package' => $package->slug]) }}" class="btn-secondary flex-1 py-2.5 text-sm">View details</a>
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="service-enquire-btn btn-accent flex-1 py-2.5 text-sm">{{ __('packages.packages.enquire') }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</section>

<style>
    .service-package-card a.service-enquire-btn {
        background-color: #f4d67b;
        border: 1px solid #d8b75a;
        color: #17372f;
        font-weight: 700;
        text-decoration: none;
    }

    .service-package-card a.service-enquire-btn:hover {
        background-color: #ffe69a;
        border-color: #cda53e;
        color: #102b25;
    }

    .service-package-card {
        border: 1px solid #d5ad45 !important;
        border-radius: 6px !important;
    }

    .service-package-card:hover {
        border-color: #b98a20 !important;
    }

    .service-package-card-image,
    .service-package-artwork {
        display: block;
        width: 100%;
        height: 132px !important;
        min-height: 132px;
        flex: 0 0 132px;
        object-fit: cover;
    }

    .service-package-card-content {
        gap: 7px;
        padding: 12px !important;
    }

    .service-package-card-content h3,
    .service-package-card-content p {
        margin-top: 0;
    }

    .service-package-card-content > p {
        display: -webkit-box;
        overflow: hidden;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
    }

    .service-package-card-content > div {
        margin-top: 5px !important;
        padding-top: 8px !important;
    }

    .service-package-card-content > div:last-child {
        margin-top: 6px !important;
    }

    .service-package-card-uncategorized .service-package-card-image,
    .service-package-artwork-tall {
        height: 140px !important;
        min-height: 140px;
        flex-basis: 140px;
    }

    .service-package-artwork {
        border-bottom: 1px solid #e2e8e2;
        background: #edf2ec;
    }

    .service-package-artwork-tall {
        height: 140px;
    }

    .services-hero {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #111827 100%);
        color: #ffffff;
        padding: 16px 0 14px;
        border-bottom: 1px solid #334155;
    }

    .services-hero-inner {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 20px;
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        align-items: center;
        gap: 20px;
    }

    .services-hero-copy {
        color: #fff;
    }

    .services-hero-badge {
        display: inline-flex;
        align-items: center;
        border: 1px solid rgba(251, 191, 36, 0.5);
        background: rgba(251, 191, 36, 0.12);
        color: #fcd34d;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
    }

    .services-hero-copy h1 {
        margin: 12px 0 0;
        font-size: clamp(1.9rem, 3.5vw, 2.7rem);
        line-height: 1.08;
        color: #ffffff;
        font-weight: 900;
        letter-spacing: -0.04em;
    }

    .services-hero-copy p {
        margin: 10px 0 0;
        color: #cbd5e1;
        font-size: 0.96rem;
        line-height: 1.6;
        max-width: 560px;
    }

    .services-hero-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        margin-top: 16px;
    }

    .services-primary-btn,
    .services-secondary-btn,
    .services-card-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 999px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .services-primary-btn {
        background: #fbbf24;
        color: #111827;
        padding: 10px 17px;
        box-shadow: 0 10px 25px rgba(251, 191, 36, 0.25);
    }

    .services-primary-btn:hover {
        background: #facc15;
    }

    .services-secondary-btn {
        background: rgba(255,255,255,0.05);
        color: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.6);
        padding: 10px 17px;
    }

    .services-secondary-btn:hover {
        background: rgba(255,255,255,0.08);
    }

    .services-hero-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-top: 14px;
        color: #cbd5e1;
    }

    .services-hero-stats div {
        display: flex;
        flex-direction: column;
        gap: 3px;
    }

    .services-hero-stats strong {
        color: #fff;
        font-size: 1.5rem;
        line-height: 1;
    }

    .services-hero-stats span {
        font-size: 0.82rem;
    }

    .services-feature-card {
        background: #ffffff;
        color: #0f172a;
        border-radius: 28px;
        padding: 14px;
        box-shadow: 0 24px 50px rgba(15, 23, 42, 0.25);
    }

    .services-feature-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.15em;
        font-weight: 700;
    }

    .services-feature-pill {
        background: #fef3c7;
        color: #92400e;
        border-radius: 999px;
        padding: 5px 8px;
    }

    .services-feature-price {
        margin-top: 12px;
    }

    .services-price {
        font-size: 1.8rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
    }

    .services-name {
        margin-top: 5px;
        font-size: 0.92rem;
        color: #64748b;
    }

    .services-feature-card ul {
        list-style: none;
        padding: 0;
        margin: 14px 0 0;
        display: grid;
        gap: 8px;
        color: #475569;
        font-size: 0.95rem;
    }

    .services-feature-card li {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .services-feature-card li::before {
        content: "";
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: #fbbf24;
        display: inline-block;
    }

    .services-card-btn {
        margin-top: 16px;
        width: 100%;
        background: #0f172a;
        color: #ffffff;
        padding: 10px 16px;
    }

    .services-card-btn:hover {
        background: #1e293b;
    }

    .services-currency-wrap {
        max-width: 1200px;
        margin: 12px auto 0;
        padding: 0 20px;
        display: flex;
        justify-content: center;
    }

    .services-currency-box {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        background: rgba(255,255,255,0.04);
        color: #fff;
        border-radius: 999px;
        padding: 6px 12px;
        font-size: 0.9rem;
    }

    .services-currency-box .active {
        color: #ffffff;
        font-weight: 700;
    }

    .services-toggle {
        position: relative;
        display: inline-block;
        width: 46px;
        height: 24px;
    }

    .services-toggle input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .services-toggle-slider {
        position: absolute;
        inset: 0;
        border-radius: 999px;
        background: #475569;
        transition: 0.2s;
    }

    .services-toggle-slider::before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: 0.2s;
    }

    .services-toggle input:checked + .services-toggle-slider {
        background: #fbbf24;
    }

    .services-toggle input:checked + .services-toggle-slider::before {
        transform: translateX(22px);
    }

    @media (max-width: 900px) {
        .services-hero-inner {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-toggle-packages]').forEach(function (button) {
            button.addEventListener('click', function () {
                const targetId = this.getAttribute('data-target');
                const target = targetId ? document.getElementById(targetId) : null;

                if (!target) {
                    return;
                }

                const hiddenCards = target.querySelectorAll('.service-hidden-card');
                const expanded = this.getAttribute('aria-expanded') === 'true';

                hiddenCards.forEach(function (card) {
                    card.classList.toggle('hidden', expanded);
                });

                this.setAttribute('aria-expanded', String(!expanded));
                this.textContent = expanded ? 'View more' : 'View less';
            });
        });
    });
</script>

@endsection
