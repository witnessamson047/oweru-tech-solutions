@extends('layouts.public')

@section('title', __('about.header.badge') . ' - Oweru Tech Solutions')

@section('content')

{{-- Page header --}}
<section class="bg-gray-50 border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-center">
        <span class="badge-gold mb-4">{{ __('about.header.badge') }}</span>
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">{{ __('about.header.title') }}</h1>
        <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-2xl mx-auto">{{ __('about.header.lead') }}</p>
    </div>
</section>

{{-- Our story --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 lg:gap-16 items-center">
            <div>
                <span class="badge-gold mb-4">{{ __('about.story.badge') }}</span>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('about.story.title') }}</h2>
                <div class="mt-5 space-y-4 text-sm sm:text-base text-gray-600 leading-relaxed">
                    <p>{{ __('about.story.p1') }}</p>
                    <p>{{ __('about.story.p2') }}</p>
                    <p>{{ __('about.story.p3') }}</p>
                </div>
            </div>
            <div class="rounded-2xl overflow-hidden border border-gray-200 shadow-sm">
                <img src="{{ asset('images/hero/team-collaboration.jpg') }}" alt="The Oweru team collaborating around a table" class="w-full h-64 sm:h-80 object-cover" loading="lazy">
            </div>
        </div>
    </div>
</section>

{{-- Mission & Vision --}}
<section class="py-16 sm:py-20 bg-gray-50 border-y border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-8">
                <span class="w-11 h-11 rounded-xl bg-yellow-50 text-yellow-700 flex items-center justify-center mb-5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </span>
                <h2 class="text-xl font-semibold text-gray-900">{{ __('about.mission.title') }}</h2>
                <p class="mt-3 text-sm sm:text-base text-gray-600 leading-relaxed">{{ __('about.mission.text') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-8">
                <span class="w-11 h-11 rounded-xl bg-yellow-50 text-yellow-700 flex items-center justify-center mb-5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </span>
                <h2 class="text-xl font-semibold text-gray-900">{{ __('about.mission.vision_title') }}</h2>
                <p class="mt-3 text-sm sm:text-base text-gray-600 leading-relaxed">{{ __('about.mission.vision_text') }}</p>
            </div>
        </div>
    </div>
</section>

{{-- Values --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-12">
            <span class="badge-gold mb-4">{{ __('about.values.badge') }}</span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('about.values.title') }}</h2>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['title' => __('about.values.honesty.title'), 'desc' => __('about.values.honesty.desc'), 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['title' => __('about.values.clarity.title'), 'desc' => __('about.values.clarity.desc'), 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
                ['title' => __('about.values.pricing.title'), 'desc' => __('about.values.pricing.desc'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['title' => __('about.values.partnership.title'), 'desc' => __('about.values.partnership.desc'), 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ] as $value)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <span class="w-10 h-10 rounded-lg bg-yellow-50 text-yellow-700 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $value['icon'] }}"/></svg>
                    </span>
                    <h3 class="text-base font-semibold text-gray-900">{{ $value['title'] }}</h3>
                    <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $value['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Why choose us — mirror of the homepage stats, expanded --}}
<section class="py-16 sm:py-20 bg-gray-950">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl mb-12">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-1.5 text-xs font-semibold text-yellow-400 mb-4">{{ __('about.why.badge') }}</span>
            <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ __('about.why.title') }}</h2>
        </div>
        <dl class="grid grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
            @foreach([
                ['value' => '100+', 'label' => __('home.stats.projects')],
                ['value' => '50+', 'label' => __('home.stats.businesses')],
                ['value' => '99.9%', 'label' => __('home.stats.uptime')],
                ['value' => '24/7', 'label' => __('home.stats.support_year')],
            ] as $stat)
                <div>
                    <dd class="text-3xl sm:text-4xl font-bold text-white">{{ $stat['value'] }}</dd>
                    <dt class="mt-1 text-sm text-gray-400">{{ $stat['label'] }}</dt>
                </div>
            @endforeach
        </dl>
    </div>
</section>

{{-- CTA --}}
<section class="py-16 sm:py-20 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">{{ __('about.cta.title') }}</h2>
        <p class="mt-4 text-sm sm:text-base text-gray-600">{{ __('about.cta.lead') }}</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('scanner.index') }}" class="btn-accent text-base px-6 py-3.5">{{ __('about.cta.run_check') }}</a>
            <a href="{{ route('contact.create') }}" class="btn-secondary text-base px-6 py-3.5">{{ __('about.cta.contact') }}</a>
        </div>
    </div>
</section>

@endsection
