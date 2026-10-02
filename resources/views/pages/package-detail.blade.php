@extends('layouts.public')

@section('title', $package->name . ' - Oweru Tech Solutions')

@section('content')

@php
    $highlights = match ($package->slug) {
        'starter-website' => [
            'Responsive, mobile-ready layout',
            'Business contact and enquiry flow',
            'Light SEO setup and analytics ready',
            'Fast launch for new brands',
        ],
        'professional-portfolio' => [
            'Premium portfolio experience',
            'Service pages and conversion sections',
            'Lead generation and contact funnels',
            'Brand-focused content structure',
        ],
        'business-website' => [
            'Custom pages for products and services',
            'Client trust-building sections',
            'CMS-friendly content management',
            'Conversion-focused journey design',
        ],
        'ecommerce-starter' => [
            'Product catalog layout',
            'Checkout and order flow setup',
            'Catalog and inventory structure',
            'Storefront polish and page optimization',
        ],
        'corporate-platform' => [
            'Multi-module business workflows',
            'Dashboard and operations automation',
            'Scalable architecture for growth',
            'Advanced reporting and integrations',
        ],
        default => [
            'Custom design and structure',
            'Responsive mobile experience',
            'Launch support and handover',
            'Flexible growth-ready setup',
        ],
    };

    $bestFor = match ($package->slug) {
        'starter-website' => 'Founders and freelancers who need a clean, credible online presence quickly.',
        'professional-portfolio' => 'Service businesses that want a polished brand and a stronger lead pipeline.',
        'business-website' => 'Companies that need a growth-focused website with clear product and service messaging.',
        'ecommerce-starter' => 'New stores looking to sell products online without complexity.',
        'corporate-platform' => 'Growing teams needing a more robust digital operations foundation.',
        default => 'Businesses that want a modern, reliable, and conversion-focused web presence.',
    };
@endphp

<section class="pkg-page-hero">
    <div class="pkg-shell">
        <div class="pkg-hero-grid">
            <div class="pkg-copy">
                <span class="pkg-kicker">{{ $package->serviceLine?->name ?? 'Service package' }}</span>
                <h1>{{ $package->name }}</h1>
                <p>{{ $package->description }}</p>

                <div class="pkg-badges">
                    <span>Responsive</span>
                    <span>Conversion-focused</span>
                    <span>Launch-ready</span>
                </div>

                <div class="pkg-stats">
                    <div>
                        <strong>{{ $currency === 'USD' ? '$' . number_format($package->price_usd, 2) : 'TZS ' . number_format($package->price_tzs) }}</strong>
                        <span>Starting price</span>
                    </div>
                    <div>
                        <strong>{{ $package->delivery_days }} days</strong>
                        <span>Typical delivery</span>
                    </div>
                </div>

                <div class="pkg-actions">
                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-primary">Request package</a>
                    <a href="{{ route('packages.index') }}" class="btn-secondary">All services</a>
                </div>
            </div>

            <aside class="pkg-card">
                <span class="pkg-card-tag">{{ $package->is_featured ? 'Featured' : 'Flexible' }}</span>
                <h2>What’s included</h2>
                <ul>
                    @foreach($highlights as $highlight)
                        <li>{{ $highlight }}</li>
                    @endforeach
                </ul>
                <a href="{{ route('payment.checkout', ['type' => 'package', 'id' => $package->id]) }}" class="btn-accent pkg-card-cta">Pay now</a>
            </aside>
        </div>
    </div>
</section>

<section class="pkg-details">
    <div class="pkg-shell">
        <div class="pkg-panel-grid">
            <article class="pkg-panel">
                <span class="pkg-mini-label">What you get</span>
                <h2>Everything you need to launch with confidence.</h2>
                <ul class="pkg-check-list">
                    @foreach($highlights as $highlight)
                        <li>{{ $highlight }}</li>
                    @endforeach
                    <li>Clean mobile-first user experience</li>
                    <li>Simple content structure for easy future updates</li>
                    <li>Friendly launch support and handover</li>
                </ul>
            </article>

            <article class="pkg-panel pkg-panel-warm">
                <span class="pkg-mini-label">Best fit</span>
                <h2>{{ $package->name }}</h2>
                <p>{{ $bestFor }}</p>

                <div class="pkg-small-metric">
                    <strong>{{ $package->delivery_days }}</strong>
                    <span>Delivery window</span>
                </div>
            </article>
        </div>
    </div>
</section>

<section class="pkg-process">
    <div class="pkg-shell">
        <div class="pkg-process-header">
            <span class="pkg-mini-label">Process</span>
            <h2>Simple, clear, and easy to manage.</h2>
        </div>

        <div class="pkg-step-grid">
            <div class="pkg-step">
                <span>01</span>
                <h3>Discovery</h3>
                <p>We align on goals, audience, and the business problem you want solved.</p>
            </div>
            <div class="pkg-step">
                <span>02</span>
                <h3>Design</h3>
                <p>We build a clean structure that feels clear, professional, and easy to use.</p>
            </div>
            <div class="pkg-step">
                <span>03</span>
                <h3>Launch</h3>
                <p>We polish, test, and hand over a product ready for real users and growth.</p>
            </div>
        </div>
    </div>
</section>

@if($relatedPackages->isNotEmpty())
    <section class="pkg-related">
        <div class="pkg-shell">
            <div class="pkg-related-head">
                <span class="pkg-mini-label">More options</span>
                <h2>Explore related services</h2>
            </div>

            <div class="pkg-related-grid">
                @foreach($relatedPackages as $related)
                    <a href="{{ route('packages.show', ['package' => $related->slug]) }}" class="pkg-related-card">
                        <span>{{ $related->serviceLine?->name ?? 'Service' }}</span>
                        <h3>{{ $related->name }}</h3>
                        <p>{{ $related->description }}</p>
                        <strong>{{ $currency === 'USD' ? '$' . number_format($related->price_usd, 2) : 'TZS ' . number_format($related->price_tzs) }}</strong>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
@endif

<section class="pkg-cta-band">
    <div class="pkg-shell pkg-cta-inner">
        <div>
            <span class="pkg-mini-label pkg-mini-label-light">Ready?</span>
            <h2>Let’s build something that feels right for your business.</h2>
        </div>
        <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-primary">Start your project</a>
    </div>
</section>

<style>
    .pkg-page-hero {
        background: linear-gradient(135deg, #17372f 0%, #102b25 100%);
        color: #fff;
        padding: 72px 0 40px;
    }

    .pkg-shell {
        width: min(1120px, calc(100% - 32px));
        margin: 0 auto;
    }

    .pkg-hero-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(280px, 0.8fr);
        gap: 26px;
        align-items: center;
    }

    .pkg-kicker,
    .pkg-mini-label,
    .pkg-card-tag {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 7px 12px;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .pkg-kicker,
    .pkg-card-tag {
        background: rgba(244, 214, 123, 0.14);
        color: #f4d67b;
    }

    .pkg-copy h1 {
        margin: 18px 0 14px;
        color: #fff;
        font-size: clamp(2.3rem, 5vw, 4rem);
        line-height: 1.02;
        letter-spacing: -0.04em;
    }

    .pkg-copy p {
        margin: 0;
        max-width: 620px;
        color: rgba(239, 246, 242, 0.92);
        line-height: 1.7;
        font-size: 1rem;
    }

    .pkg-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 22px;
    }

    .pkg-badges span {
        display: inline-flex;
        padding: 8px 12px;
        border-radius: 999px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.12);
        color: rgba(255,255,255,0.9);
        font-size: 0.76rem;
    }

    .pkg-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        margin-top: 28px;
    }

    .pkg-stats div {
        min-width: 170px;
        padding: 16px 18px;
        border-radius: 16px;
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.03);
    }

    .pkg-stats strong {
        display: block;
        font-size: 1.25rem;
        color: #fff;
    }

    .pkg-stats span {
        display: block;
        margin-top: 6px;
        font-size: 0.72rem;
        color: rgba(255,255,255,0.68);
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .pkg-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-top: 28px;
    }

    .pkg-actions .btn-primary,
    .pkg-card .btn-accent,
    .pkg-cta-inner .btn-primary {
        background-color: #f4d67b;
        color: #17372f;
        border: 1px solid #f4d67b;
        font-weight: 700;
    }

    .pkg-actions .btn-primary:hover,
    .pkg-card .btn-accent:hover,
    .pkg-cta-inner .btn-primary:hover {
        background-color: #ffe69a;
        border-color: #ffe69a;
        color: #102b25;
    }

    .pkg-card {
        padding: 22px 20px 18px;
        border-radius: 22px;
        background: rgba(8, 20, 18, 0.38);
        border: 1px solid rgba(255,255,255,0.12);
        box-shadow: 0 22px 30px rgba(0,0,0,0.12);
    }

    .pkg-card h2 {
        margin: 16px 0 12px;
        font-size: 1.3rem;
        color: #fff;
    }

    .pkg-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 12px;
    }

    .pkg-card li {
        position: relative;
        padding-left: 18px;
        color: rgba(241, 245, 243, 0.94);
        line-height: 1.6;
    }

    .pkg-card li::before {
        content: "";
        position: absolute;
        left: 0;
        top: 9px;
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: #f4d67b;
    }

    .pkg-card-cta {
        width: 100%;
        justify-content: center;
        margin-top: 20px;
    }

    .pkg-details,
    .pkg-process,
    .pkg-related {
        background: #f8f7f2;
        padding: 72px 0;
    }

    .pkg-panel-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.2fr) minmax(260px, 0.8fr);
        gap: 22px;
    }

    .pkg-panel {
        padding: 30px 28px;
        border-radius: 24px;
        background: #fff;
        border: 1px solid #e6dfc8;
        box-shadow: 0 16px 24px rgba(19, 28, 24, 0.04);
    }

    .pkg-panel-warm {
        background: linear-gradient(180deg, #fffaf0 0%, #fff 100%);
    }

    .pkg-panel h2 {
        margin: 12px 0 16px;
        color: #1b312a;
        font-size: clamp(1.5rem, 3vw, 2.2rem);
    }

    .pkg-mini-label {
        background: rgba(23, 55, 47, 0.06);
        color: #1d4b3f;
    }

    .pkg-mini-label-light {
        background: rgba(255,255,255,0.1);
        color: #f8e7a7;
    }

    .pkg-check-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        gap: 12px;
    }

    .pkg-check-list li {
        padding-left: 22px;
        position: relative;
        color: #53615b;
        line-height: 1.7;
    }

    .pkg-check-list li::before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #1d5b48;
        font-weight: 800;
    }

    .pkg-panel p {
        margin: 0;
        color: #53615b;
        line-height: 1.8;
    }

    .pkg-small-metric {
        display: grid;
        gap: 6px;
        margin-top: 22px;
        padding-top: 20px;
        border-top: 1px solid #e7e1ce;
    }

    .pkg-small-metric strong {
        color: #17372f;
        font-size: 2rem;
        line-height: 1;
    }

    .pkg-small-metric span {
        color: #66756d;
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .pkg-process-header {
        margin-bottom: 22px;
    }

    .pkg-process-header h2,
    .pkg-related-head h2 {
        margin: 10px 0 0;
        color: #1b312a;
        font-size: clamp(1.9rem, 3vw, 2.6rem);
    }

    .pkg-step-grid,
    .pkg-related-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
    }

    .pkg-step {
        padding: 24px 20px;
        border-radius: 20px;
        background: linear-gradient(180deg, #fff 0%, #faf8f1 100%);
        border: 1px solid #e7dfc5;
    }

    .pkg-step span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        background: #17372f;
        color: #f4d67b;
        font-size: 0.8rem;
        font-weight: 800;
    }

    .pkg-step h3 {
        margin: 18px 0 10px;
        color: #1b312a;
        font-size: 1.15rem;
    }

    .pkg-step p,
    .pkg-related-card p {
        margin: 0;
        color: #5d6c66;
        line-height: 1.7;
        font-size: 0.9rem;
    }

    .pkg-related-card {
        display: block;
        padding: 22px 20px;
        border-radius: 18px;
        border: 1px solid #e7dfc5;
        background: linear-gradient(180deg, #fff 0%, #faf8f1 100%);
        text-decoration: none;
        color: #1b312a;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .pkg-related-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 26px rgba(18, 28, 24, 0.06);
    }

    .pkg-related-card span {
        color: #7a641e;
        font-size: 0.67rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .pkg-related-card h3 {
        margin: 12px 0 10px;
        font-size: 1.18rem;
    }

    .pkg-related-card strong {
        display: inline-block;
        margin-top: 16px;
        color: #17372f;
        font-size: 1.1rem;
    }

    .pkg-cta-band {
        background: #17372f;
        padding: 24px 0 70px;
    }

    .pkg-cta-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 28px 30px;
        border-radius: 22px;
        background: linear-gradient(135deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
        border: 1px solid rgba(255,255,255,0.12);
    }

    .pkg-cta-inner h2 {
        margin: 12px 0 0;
        color: #fff;
        font-size: clamp(1.5rem, 3vw, 2.3rem);
    }

    @media (max-width: 820px) {
        .pkg-hero-grid,
        .pkg-panel-grid,
        .pkg-step-grid,
        .pkg-related-grid {
            grid-template-columns: 1fr;
        }

        .pkg-page-hero,
        .pkg-details,
        .pkg-process,
        .pkg-related {
            padding-top: 56px;
        }
    }

    @media (max-width: 560px) {
        .pkg-shell {
            width: min(100% - 22px, 1120px);
        }

        .pkg-actions,
        .pkg-cta-inner {
            flex-direction: column;
            align-items: stretch;
        }

        .pkg-actions a,
        .pkg-card-cta,
        .pkg-cta-inner .btn-primary {
            width: 100%;
            justify-content: center;
        }
    }
</style>

@endsection
