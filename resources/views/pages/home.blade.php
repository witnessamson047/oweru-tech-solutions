@extends('layouts.public')

@section('title', 'Oweru Tech Solutions - Professional Digital Services')

@section('content')

<section class="home-hero" id="hero">
    <div class="home-hero-inner">
        <div class="home-hero-copy">
            <span class="home-eyebrow">{{ __('home.hero.badge') }}</span>
            <h1>{{ __('home.hero.title_1') }} <span>{{ __('home.hero.title_2') }}</span></h1>
            <p>{{ __('home.hero.lead') }}</p>

            <div class="home-hero-actions">
                <a href="{{ route('scanner.index') }}" class="home-button home-button-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8"/><path d="m16 16 5 5"/></svg>
                    {{ __('home.hero.free_check') }}
                </a>
                <a href="{{ route('packages.index') }}" class="home-button home-button-outline">{{ __('home.hero.services_pricing') }}</a>
            </div>

        </div>

        <figure class="home-hero-visual">
            <img src="{{ asset('images/hero/developer-coding.jpg') }}" alt="Computer displaying code during digital solution development" fetchpriority="high">
            <figcaption><span aria-hidden="true"></span> Digital solutions, built with code</figcaption>
        </figure>
    </div>
</section>

{{-- ============================================================
     TRUST STRIP — quiet stats on white
============================================================ --}}
<section class="home-proof" aria-label="Company statistics">
    <div class="home-proof-inner">
        <dl>
            @foreach([
                ['value' => '100+', 'label' => __('home.stats.projects')],
                ['value' => '50+', 'label' => __('home.stats.businesses')],
                ['value' => '99.9%', 'label' => __('home.stats.uptime')],
                ['value' => '24/7', 'label' => __('home.stats.support')],
            ] as $stat)
                <div class="home-proof-item">
                    <dt class="sr-only">{{ $stat['label'] }}</dt>
                    <dd class="home-proof-value">{{ $stat['value'] }}</dd>
                    <dd class="home-proof-label">{{ $stat['label'] }}</dd>
                </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- Services overview; package details and prices live on the Services page. --}}
<section class="home-services" id="services">
    <div class="home-content-width">
        <div class="home-services-heading">
            <div>
                <span class="home-section-kicker">{{ __('home.services.badge') }}</span>
                <h2>{{ __('home.services.title') }}</h2>
            </div>
            <p>{{ __('home.services.lead') }}</p>
        </div>

        @php
            $homeServiceImages = [
                'software-development' => ['image' => 'images/home-services/software-development.jpg', 'alt' => 'Software developer writing code on a computer'],
                'mobile-web-development' => ['image' => 'images/home-services/mobile-web-development.jpg', 'alt' => 'Mobile web experience displayed on a smartphone'],
                'crm-solutions' => ['image' => 'images/home-services/crm-solutions.jpg', 'alt' => 'Business analytics dashboard with charts'],
            ];
        @endphp

        <div class="home-service-grid">
            @foreach($serviceLines->take(3) as $line)
                @php $serviceImage = $homeServiceImages[$line->slug] ?? null; @endphp
                <a href="{{ route('packages.index') }}#{{ $line->slug }}" class="home-service-card">
                    @if($serviceImage)
                        <img src="{{ asset($serviceImage['image']) }}" alt="{{ $serviceImage['alt'] }}" loading="lazy">
                    @else
                        <div class="home-service-artwork" aria-hidden="true"></div>
                    @endif
                    <div class="home-service-card-content">
                        <h3>{{ $line->name }}</h3>
                        <p>{{ $line->description }}</p>
                        <span class="home-service-card-arrow" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="home-services-footer">
            <a href="{{ route('packages.index') }}" class="home-text-link">
                {{ __('site.common.view_all_services') }}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
            </a>
        </div>
    </div>
</section>

{{-- ============================================================
     CTA — dark, calm, one decision
============================================================ --}}
<section class="home-cta" id="cta">
    <div class="home-cta-inner">
        <div>
            <span class="home-section-kicker">{{ __('home.hero.badge') }}</span>
            <h2>{{ __('home.cta.title') }}</h2>
            <p>{{ __('home.cta.lead') }}</p>
        </div>
        <div class="home-cta-actions">
            <a href="{{ route('scanner.index') }}" class="home-button home-button-primary">{{ __('home.cta.run_check') }}</a>
            <a href="{{ route('contact.create') }}" class="home-button home-button-outline">{{ __('home.cta.contact') }}</a>
            <span>{{ __('home.cta.note') }}</span>
        </div>
    </div>
</section>

<style>
    .home-hero {
        overflow: hidden;
        background: #17372f;
        color: #fff;
    }

    .home-hero-inner,
    .home-proof-inner,
    .home-content-width,
    .home-cta-inner {
        width: min(1120px, calc(100% - 40px));
        margin: 0 auto;
    }

    .home-hero-inner {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
        align-items: center;
        gap: 36px;
        min-height: 0;
        padding: 32px 0;
    }

    .home-hero-copy {
        max-width: 590px;
    }

    .home-eyebrow,
    .home-section-kicker {
        color: #e5bd5c;
        font-size: 11px;
        font-weight: 750;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .home-hero-copy h1 {
        margin: 12px 0 0;
        color: #fff;
        font-size: 48px;
        font-weight: 760;
        line-height: 1.08;
    }

    .home-hero-copy h1 span {
        color: #edc65e;
    }

    .home-hero-copy > p {
        max-width: 510px;
        margin: 14px 0 0;
        color: #d1ded9;
        font-size: 1rem;
        line-height: 1.7;
    }

    .home-hero-actions,
    .home-cta-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 20px;
    }

    .home-button {
        display: inline-flex;
        min-height: 46px;
        align-items: center;
        justify-content: center;
        gap: 9px;
        border: 1px solid transparent;
        border-radius: 4px;
        padding: 11px 17px;
        font-size: 0.86rem;
        font-weight: 700;
        text-decoration: none;
        transition: background-color 160ms ease, border-color 160ms ease;
    }

    .home-button-primary {
        background: #e8bd50;
        color: #17251f;
    }

    .home-button-primary:hover {
        background: #f1ce70;
    }

    .home-button-outline {
        border-color: rgba(255,255,255,0.45);
        color: #fff;
    }

    .home-button-outline:hover {
        border-color: #fff;
        background: rgba(255,255,255,0.08);
    }

    .home-button svg {
        width: 18px;
        height: 18px;
        stroke-width: 1.8;
    }

    .home-hero-visual {
        position: relative;
        width: 100%;
        height: 300px;
        margin: 0;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 5px;
        background: #29483e;
    }

    .home-hero-visual img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center 45%;
    }

    .home-hero-visual figcaption {
        position: absolute;
        right: 14px;
        bottom: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255,255,255,0.35);
        border-radius: 3px;
        background: rgba(15, 33, 28, 0.86);
        padding: 8px 10px;
        color: #fff;
        font-size: 0.7rem;
    }

    .home-hero-visual figcaption span {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #e8bd50;
    }

    .home-proof {
        border-bottom: 1px solid #e3e8e3;
        background: #fff;
    }

    .home-proof-inner {
        padding: 24px 0;
    }

    .home-proof-inner dl {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        margin: 0;
    }

    .home-proof-item {
        padding: 4px 22px;
        border-right: 1px solid #e4e8e3;
    }

    .home-proof-item:first-child {
        padding-left: 0;
    }

    .home-proof-item:last-child {
        border-right: 0;
    }

    .home-proof-value,
    .home-proof-label {
        margin: 0;
    }

    .home-proof-value {
        color: #203a30;
        font-size: 1.6rem;
        font-weight: 750;
        line-height: 1.1;
    }

    .home-proof-label {
        margin-top: 5px;
        color: #707c75;
        font-size: 0.75rem;
    }

    .home-services {
        padding: 72px 0 66px;
        background: #f6f7f4;
    }

    .home-services-heading {
        display: grid;
        grid-template-columns: 1fr minmax(260px, 0.75fr);
        align-items: end;
        gap: 45px;
        margin-bottom: 30px;
    }

    .home-services-heading h2,
    .home-cta h2 {
        margin: 9px 0 0;
        color: #1b3028;
        font-size: clamp(1.8rem, 3vw, 2.5rem);
        font-weight: 750;
        line-height: 1.15;
    }

    .home-services-heading > p {
        margin: 0;
        color: #657169;
        font-size: 0.92rem;
        line-height: 1.65;
    }

    .home-service-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .home-service-card {
        display: flex;
        min-width: 0;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid #dce2dc;
        border-radius: 5px;
        background: #fff;
        color: #263a31;
        text-decoration: none;
        transition: border-color 160ms ease, transform 160ms ease;
    }

    .home-service-card:hover {
        border-color: #aebfb2;
        transform: translateY(-2px);
    }

    .home-service-card > img,
    .home-service-artwork {
        display: block;
        width: 100%;
        height: 170px;
        object-fit: cover;
        background: #e8eee8;
    }

    .home-service-card-content {
        display: grid;
        flex: 1;
        grid-template-rows: auto 1fr auto;
        gap: 9px;
        padding: 16px 17px;
    }

    .home-service-card-content h3 {
        margin: 0;
        color: #20372e;
        font-size: 1rem;
        font-weight: 700;
        line-height: 1.35;
    }

    .home-service-card-content p {
        display: -webkit-box;
        overflow: hidden;
        margin: 0;
        color: #68756d;
        font-size: 0.82rem;
        line-height: 1.55;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 3;
    }

    .home-service-card-arrow {
        display: flex;
        justify-content: flex-end;
        color: #285b49;
    }

    .home-service-card-arrow svg,
    .home-text-link svg {
        width: 18px;
        height: 18px;
        flex: 0 0 auto;
        stroke-width: 1.7;
    }

    .home-services-footer {
        display: flex;
        justify-content: flex-end;
        margin-top: 22px;
    }

    .home-text-link {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: #285b49;
        font-size: 0.85rem;
        font-weight: 700;
        text-decoration: none;
    }

    .home-text-link:hover {
        color: #18372d;
    }

    .home-cta {
        background: #17372f;
        color: #fff;
    }

    .home-cta-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 40px;
        padding: 48px 0;
    }

    .home-cta h2 {
        max-width: 570px;
        color: #fff;
        font-size: clamp(1.7rem, 3vw, 2.35rem);
    }

    .home-cta-inner > div:first-child > p {
        max-width: 580px;
        margin: 12px 0 0;
        color: #c9d5d0;
        font-size: 0.88rem;
        line-height: 1.65;
    }

    .home-cta-actions {
        flex: 0 0 auto;
        margin-top: 0;
    }

    .home-cta-actions > span {
        flex-basis: 100%;
        color: #aabbb3;
        font-size: 0.68rem;
    }

    @media (max-width: 800px) {
        .home-hero-inner {
            grid-template-columns: 1fr 0.9fr;
            gap: 24px;
            min-height: 0;
        }

        .home-hero-visual {
            height: 260px;
        }

        .home-hero-copy h1 {
            font-size: 40px;
        }

        .home-service-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .home-service-card > img,
        .home-service-artwork {
            height: 150px;
        }

        .home-cta-inner {
            align-items: flex-start;
            flex-direction: column;
            gap: 12px;
        }
    }

    @media (max-width: 600px) {
        .home-hero-inner,
        .home-proof-inner,
        .home-content-width,
        .home-cta-inner {
            width: min(100% - 32px, 1120px);
        }

        .home-hero-inner {
            grid-template-columns: 1fr;
            gap: 14px;
            padding: 20px 0 18px;
        }

        .home-hero-copy h1 {
            font-size: 32px;
        }

        .home-hero-copy > p {
            font-size: 0.92rem;
            line-height: 1.55;
        }

        .home-hero-actions {
            flex-wrap: nowrap;
            gap: 8px;
        }

        .home-hero-actions .home-button {
            min-width: 0;
            min-height: 42px;
            flex: 1 1 0;
            gap: 6px;
            padding: 9px 8px;
            font-size: 0.74rem;
            white-space: nowrap;
        }

        .home-hero-visual {
            height: auto;
            aspect-ratio: 2 / 1;
        }

        .home-proof-inner dl {
            grid-template-columns: repeat(2, 1fr);
            gap: 18px 0;
        }

        .home-proof-item {
            padding: 4px 12px;
        }

        .home-proof-item:nth-child(2) {
            border-right: 0;
        }

        .home-proof-item:nth-child(odd) {
            padding-left: 0;
        }

        .home-services {
            padding: 48px 0;
        }

        .home-services-heading {
            grid-template-columns: 1fr;
            gap: 12px;
            margin-bottom: 22px;
        }

        .home-service-grid {
            grid-template-columns: 1fr;
        }

        .home-service-card > img,
        .home-service-artwork {
            height: 180px;
        }

        .home-services-footer {
            justify-content: flex-start;
        }

        .home-cta-inner {
            padding: 38px 0;
        }

        .home-cta-actions {
            width: 100%;
        }

        .home-cta-actions .home-button {
            flex: 1;
        }
    }
</style>

@endsection
