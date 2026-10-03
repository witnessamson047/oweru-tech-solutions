@extends('layouts.public')

@section('title', __('faq.header.badge') . ' - Oweru Tech Solutions')

@section('content')

{{-- Page header --}}
<section class="bg-gray-50 border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-center">
        <span class="badge-gold mb-4">{{ __('faq.header.badge') }}</span>
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">{{ __('faq.header.title') }}</h1>
        <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-2xl mx-auto">
            {{ __('faq.header.lead') }}
            <a href="{{ route('contact.create') }}" class="text-yellow-700 font-medium hover:underline">{{ __('faq.header.ask') }}</a>.
        </p>
    </div>
</section>

@php
    $faqGroups = [
        __('faq.groups.start') => __('faq.getting_started'),
        __('faq.groups.work') => __('faq.working_with_us'),
        __('faq.groups.payments') => __('faq.payments'),
    ];
@endphp

<section class="py-14 sm:py-16">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        @foreach($faqGroups as $group => $faqs)
            <div class="mb-10 last:mb-0">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">{{ $group }}</h2>
                <div class="divide-y divide-gray-100 border border-gray-200 rounded-2xl bg-white">
                    @foreach($faqs as $faq)
                        <details class="group px-5 sm:px-6" {{ $loop->parent->first && $loop->first ? 'open' : '' }}>
                            <summary class="flex items-center justify-between gap-4 py-4 cursor-pointer list-none [&::-webkit-details-marker]:hidden">
                                <span class="text-sm sm:text-base font-medium text-gray-900">{{ $faq['q'] }}</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0 transition-transform duration-200 group-open:rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            </summary>
                            <p class="pb-5 pr-8 text-sm text-gray-600 leading-relaxed">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        @endforeach

        {{-- Still have questions --}}
        <div class="mt-12 bg-gray-50 border border-gray-100 rounded-2xl p-8 text-center">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('faq.still.title') }}</h2>
            <p class="mt-2 text-sm text-gray-600">{{ __('faq.still.lead') }}</p>
            <a href="{{ route('contact.create') }}" class="btn-accent mt-6">{{ __('faq.still.button') }}</a>
        </div>

    </div>
</section>

@endsection
