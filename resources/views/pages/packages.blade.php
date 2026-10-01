@extends('layouts.public')

@section('title', __('packages.header.badge') . ' - Oweru Tech Solutions')

@section('content')

{{-- Page header — clean white with gold badge --}}
<section class="bg-gray-50 border-b border-gray-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-center">
        <span class="badge-gold mb-4">{{ __('packages.header.badge') }}</span>
        <h1 class="text-3xl sm:text-4xl font-bold tracking-tight text-gray-900">{{ __('packages.header.title') }}</h1>
        <p class="mt-4 text-sm sm:text-base text-gray-600 max-w-2xl mx-auto">{{ __('packages.header.lead') }}</p>

        {{-- Currency toggle --}}
        <div class="mt-7 inline-flex items-center gap-3">
            <span class="text-sm font-medium {{ !$currency || $currency === 'TZS' ? 'text-gray-900' : 'text-gray-400' }}">TZS</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="currency-toggle" class="sr-only peer" {{ $currency === 'USD' ? 'checked' : '' }} aria-label="{{ __('packages.header.show_usd') }}">
                <div class="w-11 h-6 bg-gray-300 rounded-full peer peer-checked:bg-gray-900 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:after:translate-x-full"></div>
            </label>
            <span class="text-sm font-medium {{ $currency === 'USD' ? 'text-gray-900' : 'text-gray-400' }}">USD</span>
        </div>
    </div>
</section>

<section class="py-12 sm:py-16">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        @php
            $serviceLines = \App\Models\ServiceLine::active()->get();
            $uncategorised = $packages->whereNull('service_line_id');
            $packageImages = [
                'software-development' => ['image' => 'images/services/software-development.jpg', 'alt' => 'Developer writing code across two monitors'],
                'mobile-web-development' => ['image' => 'images/services/mobile-web-development.jpg', 'alt' => 'Responsive website design displayed on laptop and phone'],
                'crm-solutions' => ['image' => 'images/services/crm-solutions.jpg', 'alt' => 'Sales dashboard with charts on a computer screen'],
                'network-design' => ['image' => 'images/services/network-design.jpg', 'alt' => 'Technician configuring network equipment'],
            ];
            $defaultPackageImage = ['image' => 'images/hero/team-collaboration.jpg', 'alt' => 'Oweru team collaborating around a table'];
        @endphp

        @foreach($serviceLines as $line)
            @php $groupPackages = $packages->where('service_line_id', $line->id); @endphp
            <div id="{{ $line->slug }}" class="mb-14 last:mb-0 service-line-target scroll-mt-24">
                <div class="mb-6 pb-5 border-b border-gray-100">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">{{ $line->name }}</h2>
                    <p class="mt-1 text-sm text-gray-600">{{ $line->description }}</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @forelse($groupPackages as $package)
                        @php $meta = $packageImages[$package->slug] ?? $defaultPackageImage; @endphp
                        <article class="card-hover group relative flex flex-col bg-white rounded-2xl border overflow-hidden {{ $package->is_featured ? 'border-yellow-500 shadow-md' : 'border-gray-200' }}">
                            @if($package->is_featured)
                                <span class="absolute top-4 right-4 bg-yellow-100 text-yellow-900 text-[11px] font-semibold px-2.5 py-1 rounded-full">{{ __('packages.packages.recommended') }}</span>
                            @endif

                            <img src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="w-full h-40 object-cover transition-transform duration-500 group-hover:scale-[1.04]" loading="lazy">

                            <div class="flex flex-col flex-1 p-6">
                                <h3 class="text-base font-semibold text-gray-900">{{ $package->name }}</h3>
                                <p class="mt-2 text-sm text-gray-600 leading-relaxed flex-1">{{ $package->description }}</p>

                                <div class="mt-5 flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                    <span class="text-xl font-bold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ __('packages.packages.delivery_days', ['days' => $package->delivery_days]) }}</p>

                                <div class="mt-5 flex items-center gap-2">
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-secondary flex-1 py-2.5 text-sm">{{ __('packages.packages.enquire') }}</a>
                                    <a href="{{ route('payment.checkout', ['type' => 'package', 'id' => $package->id]) }}" class="btn-accent flex-1 py-2.5 text-sm">{{ __('packages.packages.pay_now') }}</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p class="text-sm text-gray-500">{{ __('packages.packages.empty') }} <a href="{{ route('enquiry.create') }}" class="text-yellow-700 font-medium hover:underline">{{ __('packages.packages.empty_link') }}</a>.</p>
                    @endforelse
                </div>
            </div>
        @endforeach

        @if($uncategorised->isNotEmpty())
            <div class="mb-14">
                <div class="mb-6 pb-5 border-b border-gray-100">
                    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">{{ __('packages.packages.other') }}</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                    @foreach($uncategorised as $package)
                        @php $meta = $packageImages[$package->slug] ?? $defaultPackageImage; @endphp
                        <article class="card-hover group relative flex flex-col bg-white rounded-2xl border overflow-hidden {{ $package->is_featured ? 'border-yellow-500 shadow-md' : 'border-gray-200' }}">
                            @if($package->is_featured)
                                <span class="absolute top-4 right-4 bg-yellow-100 text-yellow-900 text-[11px] font-semibold px-2.5 py-1 rounded-full">{{ __('packages.packages.recommended') }}</span>
                            @endif

                            <img src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="w-full h-40 object-cover transition-transform duration-500 group-hover:scale-[1.04]" loading="lazy">

                            <div class="flex flex-col flex-1 p-6">
                                <h3 class="text-base font-semibold text-gray-900">{{ $package->name }}</h3>
                                <p class="mt-2 text-sm text-gray-600 leading-relaxed flex-1">{{ $package->description }}</p>

                                <div class="mt-5 flex items-baseline gap-2">
                                    <span class="text-xl font-bold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                    <span class="text-xl font-bold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">{{ __('packages.packages.delivery_days', ['days' => $package->delivery_days]) }}</p>

                                <div class="mt-5 flex items-center gap-2">
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-secondary flex-1 py-2.5 text-sm">{{ __('packages.packages.enquire') }}</a>
                                    <a href="{{ route('payment.checkout', ['type' => 'package', 'id' => $package->id]) }}" class="btn-accent flex-1 py-2.5 text-sm">{{ __('packages.packages.pay_now') }}</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Care plans --}}
        <div id="care-plans" class="scroll-mt-24">
            <div class="mb-6 pb-5 border-b border-gray-100">
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-gray-900">{{ __('packages.care.title') }}</h2>
                <p class="mt-1 text-sm text-gray-600">{{ __('packages.care.lead') }}</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($carePlans as $plan)
                    @php $isFeatured = $plan->is_featured; @endphp
                    <article class="card-hover group relative flex flex-col bg-white rounded-2xl border overflow-hidden {{ $isFeatured ? 'border-yellow-500 shadow-md' : 'border-gray-200' }}">
                        @if($isFeatured)
                            <span class="absolute top-4 right-4 bg-yellow-100 text-yellow-900 text-[11px] font-semibold px-2.5 py-1 rounded-full">{{ __('packages.care.best_value') }}</span>
                        @endif

                        <div class="flex flex-col flex-1 p-6 sm:p-8">
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
                                <span class="text-sm text-gray-500">{{ __('packages.care.per_month') }}</span>
                            </div>

                            <div class="mt-5 flex items-center gap-2">
                                <a href="{{ route('enquiry.create', ['package' => $plan->slug]) }}" class="btn-secondary flex-1 py-2.5 text-sm">{{ __('packages.care.enquire') }}</a>
                                <a href="{{ route('payment.checkout', ['type' => 'care-plan', 'id' => $plan->id]) }}" class="btn-accent flex-1 py-2.5 text-sm">{{ __('packages.care.pay_now') }}</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="text-sm text-gray-500">{{ __('packages.care.empty') }} <a href="{{ route('enquiry.create') }}" class="text-yellow-700 font-medium hover:underline">{{ __('packages.care.empty_link') }}</a>.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>

@endsection
