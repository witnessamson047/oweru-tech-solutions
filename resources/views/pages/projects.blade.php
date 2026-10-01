@extends('layouts.public')

@section('title', __('projects.header.badge') . ' - Oweru Tech Solutions')

@section('content')

{{-- Page header --}}
<section class="bg-gray-50 border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-center">
        <span class="badge-gold mb-4">{{ __('projects.header.badge') }}</span>
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">{{ __('projects.header.title') }}</h1>
        <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-2xl mx-auto">{{ __('projects.header.lead') }}</p>
    </div>
</section>

@php
    /*
     * Placeholder case studies — structure per project: name, client, image,
     * alt, tags, problem, work (array), results (array). All strings come
     * from lang/{locale}/projects.php so both languages stay in sync.
     */
    $projects = [
        ['key' => 0, 'image' => 'images/services/mobile-web-development.jpg'],
        ['key' => 1, 'image' => 'images/services/crm-solutions.jpg'],
        ['key' => 2, 'image' => 'images/services/software-development.jpg'],
    ];
@endphp

{{-- Case studies --}}
<section class="py-14 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12 sm:space-y-16">

        @foreach($projects as $p)
            @php $t = __('projects.projects')[$p['key']]; @endphp
            <article class="card-hover group bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="grid grid-cols-1 lg:grid-cols-2">
                    <div class="relative order-1 lg:order-none">
                        <img src="{{ asset($p['image']) }}" alt="{{ $t['alt'] }}" class="h-56 sm:h-72 lg:h-full w-full object-cover transition-transform duration-500 group-hover:scale-[1.04]" loading="lazy">
                    </div>

                    <div class="p-6 sm:p-10">
                        <div class="flex flex-wrap gap-2">
                            @foreach($t['tags'] as $tag)
                                <span class="text-xs font-medium text-yellow-700 bg-yellow-50 border border-yellow-100 px-2.5 py-1 rounded-full">{{ $tag }}</span>
                            @endforeach
                        </div>

                        <h2 class="mt-4 text-xl sm:text-2xl font-bold tracking-tight text-gray-900">{{ $t['name'] }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $t['client'] }}</p>

                        <div class="mt-6 space-y-5">
                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">{{ __('projects.case_study.problem') }}</h3>
                                <p class="mt-1.5 text-sm text-gray-600 leading-relaxed">{{ $t['problem'] }}</p>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">{{ __('projects.case_study.what_we_did') }}</h3>
                                <ul class="mt-2 space-y-1.5">
                                    @foreach($t['work'] as $item)
                                        <li class="flex items-start gap-2.5">
                                            <svg class="w-4 h-4 mt-0.5 text-yellow-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                            <span class="text-sm text-gray-600">{{ $item }}</span>
        </li>
                                    @endforeach
                                </ul>
                            </div>

                            <div>
                                <h3 class="text-xs font-semibold text-gray-900 uppercase tracking-wider">{{ __('projects.case_study.results') }}</h3>
                                <ul class="mt-2 space-y-1.5">
                                    @foreach($t['results'] as $item)
                                        <li class="flex items-start gap-2.5">
                                            <svg class="w-4 h-4 mt-0.5 text-yellow-700 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                            <span class="text-sm text-gray-600">{{ $item }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>

                        <div class="mt-8">
                            <a href="{{ route('enquiry.create') }}" class="btn-secondary text-sm">{{ __('projects.case_study.cta') }}</a>
                        </div>
                    </div>
                </div>
            </article>
        @endforeach

    </div>
</section>

{{-- CTA --}}
<section class="bg-gray-950">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-white">{{ __('projects.cta.title') }}</h2>
        <p class="mt-4 text-sm sm:text-base text-gray-400">{{ __('projects.cta.lead') }}</p>
        <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
            <a href="{{ route('scanner.index') }}" class="btn-accent text-base px-6 py-3.5 !bg-yellow-500 !text-gray-950 hover:!bg-yellow-400">{{ __('projects.cta.start_free') }}</a>
            <a href="{{ route('contact.create') }}" class="btn-outline-light text-base px-6 py-3.5">{{ __('projects.cta.talk') }}</a>
        </div>
    </div>
</section>

@endsection
