@extends('layouts.public')

@section('title', __('about.header.badge') . ' - Oweru Tech Solutions')

@section('content')

<section class="about-hero">
    <div class="about-hero-inner">
        <div class="about-hero-copy">
            <span class="about-kicker">{{ __('about.header.badge') }}</span>
            <h1>{{ __('about.header.title') }}</h1>
            <p>{{ __('about.header.lead') }}</p>
        </div>
        <figure class="about-hero-image" data-scroll-reveal>
            <img src="{{ asset('images/hero/team-collaboration.jpg') }}" alt="The Oweru team collaborating around a table" fetchpriority="high">
            <figcaption>Oweru International Ltd</figcaption>
        </figure>
    </div>
</section>

<section class="about-story">
    <div class="about-content-width about-story-layout">
        <div class="about-story-heading">
            <span class="about-section-number">01 / {{ __('about.story.badge') }}</span>
            <h2>{{ __('about.story.title') }}</h2>
        </div>
        <div class="about-story-copy">
            <p>{{ __('about.story.p1') }}</p>
            <p>{{ __('about.story.p2') }}</p>
            <p>{{ __('about.story.p3') }}</p>
        </div>
    </div>
</section>

<section class="about-pillars">
    <div class="about-content-width about-pillars-grid">
        <article class="about-pillar">
            <span class="about-section-number">02 / Mission</span>
            <h2>{{ __('about.mission.title') }}</h2>
            <p>{{ __('about.mission.text') }}</p>
        </article>
        <article class="about-pillar">
            <span class="about-section-number">03 / Vision</span>
            <h2>{{ __('about.mission.vision_title') }}</h2>
            <p>{{ __('about.mission.vision_text') }}</p>
        </article>
    </div>
</section>

<section class="about-values">
    <div class="about-content-width">
        <div class="about-values-heading">
            <div>
                <span class="about-section-number">04 / {{ __('about.values.badge') }}</span>
                <h2>{{ __('about.values.title') }}</h2>
            </div>
        </div>
        <div class="about-values-grid">
            @foreach([
                ['title' => __('about.values.honesty.title'), 'desc' => __('about.values.honesty.desc')],
                ['title' => __('about.values.clarity.title'), 'desc' => __('about.values.clarity.desc')],
                ['title' => __('about.values.pricing.title'), 'desc' => __('about.values.pricing.desc')],
                ['title' => __('about.values.partnership.title'), 'desc' => __('about.values.partnership.desc')],
            ] as $value)
                <article class="about-value">
                    <h3>{{ $value['title'] }}</h3>
                    <p>{{ $value['desc'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="about-cta">
    <div class="about-content-width about-cta-inner">
        <div>
            <span class="about-section-number">Oweru / Let's work together</span>
            <h2>{{ __('about.cta.title') }}</h2>
            <p>{{ __('about.cta.lead') }}</p>
        </div>
        <div class="about-cta-actions">
            <a href="{{ route('scanner.index') }}" class="about-button about-button-primary">{{ __('about.cta.run_check') }}</a>
            <a href="{{ route('contact.create') }}" class="about-button about-button-outline">{{ __('about.cta.contact') }}</a>
        </div>
    </div>
</section>

<style>
    .about-content-width,
    .about-hero-inner {
        width: min(1120px, calc(100% - 40px));
        margin: 0 auto;
    }

    .about-hero {
        background: #17372f;
        color: #fff;
    }

    .about-hero-inner {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 0.95fr);
        align-items: center;
        gap: 64px;
        padding: 48px 0;
    }

    .about-kicker,
    .about-section-number {
        color: #b98732;
        font-size: 11px;
        font-weight: 750;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .about-kicker {
        color: #e7c15d;
    }

    .about-hero-copy h1 {
        max-width: 580px;
        margin: 16px 0 0;
        color: #fff;
        font-size: 48px;
        font-weight: 750;
        line-height: 1.06;
    }

    .about-hero-copy p {
        max-width: 500px;
        margin: 18px 0 0;
        color: #d0ddd7;
        font-size: 1rem;
        line-height: 1.7;
    }

    .about-hero-image {
        position: relative;
        aspect-ratio: 1.3 / 1;
        margin: 0;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 5px;
        background: #29483e;
    }

    .about-hero-image img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }

    .about-hero-image figcaption {
        position: absolute;
        right: 12px;
        bottom: 12px;
        border: 1px solid rgba(255,255,255,0.32);
        border-radius: 3px;
        background: rgba(15, 33, 28, 0.86);
        padding: 7px 10px;
        color: #fff;
        font-size: 0.68rem;
    }

    .about-story {
        padding: 56px 0;
        background: #fff;
    }

    .about-story-layout {
        display: grid;
        grid-template-columns: 0.7fr 1.3fr;
        gap: 70px;
    }

    .about-story-heading h2,
    .about-values-heading h2 {
        margin: 10px 0 0;
        color: #1b3028;
        font-size: 30px;
        font-weight: 750;
        line-height: 1.18;
    }

    .about-story-copy {
        display: grid;
        gap: 13px;
        color: #5e6b64;
        font-size: 0.94rem;
        line-height: 1.75;
    }

    .about-story-copy p {
        margin: 0;
    }

    .about-pillars {
        border-top: 1px solid #e0e6e0;
        border-bottom: 1px solid #e0e6e0;
        background: #f3f5f1;
    }

    .about-pillars-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .about-pillar {
        padding: 34px 42px 38px 0;
    }

    .about-pillar + .about-pillar {
        border-left: 1px solid #d8dfd8;
        padding-right: 0;
        padding-left: 42px;
    }

    .about-pillar h2 {
        margin: 10px 0 0;
        color: #20372e;
        font-size: 22px;
        font-weight: 700;
    }

    .about-pillar p {
        max-width: 470px;
        margin: 10px 0 0;
        color: #627068;
        font-size: 0.88rem;
        line-height: 1.7;
    }

    .about-values {
        padding: 56px 0 62px;
        background: #fff;
    }

    .about-values-heading {
        margin-bottom: 24px;
    }

    .about-values-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        border-top: 1px solid #dce3dc;
    }

    .about-value {
        padding: 18px 18px 0 0;
    }

    .about-value + .about-value {
        border-left: 1px solid #e1e6e1;
        padding-left: 18px;
    }

    .about-value h3 {
        margin: 0;
        color: #263b32;
        font-size: 0.95rem;
        font-weight: 700;
    }

    .about-value p {
        margin: 8px 0 0;
        color: #69756e;
        font-size: 0.8rem;
        line-height: 1.6;
    }

    .about-cta {
        background: #17372f;
        color: #fff;
    }

    .about-cta-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 36px;
        padding: 38px 0;
    }

    .about-cta .about-section-number {
        color: #e7c15d;
    }

    .about-cta h2 {
        margin: 8px 0 0;
        color: #fff;
        font-size: 26px;
        font-weight: 700;
        line-height: 1.2;
    }

    .about-cta p {
        max-width: 600px;
        margin: 10px 0 0;
        color: #d0ddd7;
        font-size: 0.84rem;
        line-height: 1.6;
    }

    .about-cta-actions {
        display: flex;
        flex: 0 0 auto;
        flex-wrap: wrap;
        gap: 10px;
    }

    .about-button {
        display: inline-flex;
        min-height: 42px;
        align-items: center;
        justify-content: center;
        border: 1px solid transparent;
        border-radius: 4px;
        padding: 10px 14px;
        font-size: 0.8rem;
        font-weight: 700;
        text-decoration: none;
    }

    .about-button-primary {
        background: #e8bd50;
        color: #17251f;
    }

    .about-button-primary:hover {
        background: #f1ce70;
    }

    .about-button-outline {
        border-color: rgba(255,255,255,0.45);
        color: #fff;
    }

    .about-button-outline:hover {
        border-color: #fff;
        background: rgba(255,255,255,0.08);
    }

    @media (max-width: 800px) {
        .about-hero-inner {
            gap: 30px;
        }

        .about-hero-copy h1 {
            font-size: 38px;
        }

        .about-story-layout {
            gap: 36px;
        }

        .about-values-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 22px;
        }

        .about-value:nth-child(3) {
            border-left: 0;
            padding-left: 0;
        }

        .about-cta-inner {
            align-items: flex-start;
            flex-direction: column;
            gap: 18px;
        }
    }

    @media (max-width: 600px) {
        .about-content-width,
        .about-hero-inner {
            width: min(100% - 32px, 1120px);
        }

        .about-hero-inner {
            grid-template-columns: 1fr;
            gap: 22px;
            padding: 34px 0 30px;
        }

        .about-hero-copy h1 {
            font-size: 34px;
        }

        .about-hero-image {
            aspect-ratio: 1.4 / 1;
        }

        .about-story {
            padding: 38px 0;
        }

        .about-story-layout {
            grid-template-columns: 1fr;
            gap: 16px;
        }

        .about-story-heading h2,
        .about-values-heading h2 {
            font-size: 26px;
        }

        .about-pillars-grid {
            grid-template-columns: 1fr;
        }

        .about-pillar,
        .about-pillar + .about-pillar {
            padding: 24px 0;
        }

        .about-pillar + .about-pillar {
            border-top: 1px solid #d8dfd8;
            border-left: 0;
        }

        .about-values {
            padding: 40px 0;
        }

        .about-values-grid {
            grid-template-columns: 1fr;
            row-gap: 0;
        }

        .about-value,
        .about-value + .about-value,
        .about-value:nth-child(3) {
            border-left: 0;
            border-bottom: 1px solid #e1e6e1;
            padding: 15px 0;
        }

        .about-cta-inner {
            padding: 32px 0;
        }

        .about-cta-actions {
            width: 100%;
        }

        .about-button {
            flex: 1;
        }
    }
</style>

@endsection
