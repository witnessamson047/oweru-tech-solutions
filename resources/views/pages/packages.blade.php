@extends('layouts.public')

@section('title', 'Services & Pricing - Oweru Tech Solutions')

@section('content')

{{-- Page Header --}}
<section class="relative bg-black text-white py-16 overflow-hidden">
    <img src="{{ asset('images/services/header-consultation.jpg') }}" alt="Oweru team reviewing a client project together"
        class="absolute inset-0 h-full w-full object-cover" loading="eager" />
    <div class="absolute inset-0 bg-gradient-to-b from-black/55 via-black/70 to-black"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative text-on-photo">
        <h1 class="text-3xl md:text-4xl font-bold mb-3">Services & Pricing</h1>
        <p class="text-gray-300 max-w-2xl mx-auto">Transparent pricing for every business size. Select the package that matches your needs.</p>

        {{-- Currency Toggle --}}
        <div class="flex items-center justify-center gap-3 mt-6">
            <span class="text-sm font-medium text-white">TZS</span>
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" id="currency-toggle" class="sr-only peer" {{ $currency === 'USD' ? 'checked' : '' }}>
                <div class="w-11 h-6 bg-gray-600 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-gray-400 after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-400 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-500"></div>
            </label>
            <span class="text-sm font-medium text-white">USD</span>
        </div>
    </div>
</section>

<section class="py-12 -mt-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- All Packages by Service Line --}}
        @php
            $serviceLines = \App\Models\ServiceLine::active()->get();
            $uncategorised = $packages->whereNull('service_line_id');
            $lineImages = [
                'software-development' => ['image' => 'images/services/software-development.jpg', 'alt' => 'Developer writing code across two monitors'],
                'mobile-web-development' => ['image' => 'images/services/mobile-web-development.jpg', 'alt' => 'Responsive website design displayed on laptop and phone'],
                'crm-solutions' => ['image' => 'images/services/crm-solutions.jpg', 'alt' => 'Sales dashboard with charts on a computer screen'],
                'network-design' => ['image' => 'images/services/network-design.jpg', 'alt' => 'Technician configuring network equipment'],
            ];
            $defaultLineImage = ['image' => 'images/hero/team-collaboration.jpg', 'alt' => 'Oweru team collaborating around a table'];

            // Per-package images (same mapping as the home page cards)
            $packageImages = [
                'software-development' => ['image' => 'images/services/software-development.jpg', 'alt' => 'Developer writing code across two monitors'],
                'mobile-web-development' => ['image' => 'images/services/mobile-web-development.jpg', 'alt' => 'Responsive website design displayed on laptop and phone'],
                'crm-solutions' => ['image' => 'images/services/crm-solutions.jpg', 'alt' => 'Sales dashboard with charts on a computer screen'],
                'network-design' => ['image' => 'images/services/network-design.jpg', 'alt' => 'Technician configuring network equipment'],
            ];
            $defaultPackageImage = ['image' => 'images/hero/team-collaboration.jpg', 'alt' => 'Oweru team collaborating around a table'];
        @endphp

        @foreach($serviceLines as $line)
            @php
                $groupPackages = $packages->where('service_line_id', $line->id);
                $lineImage = $lineImages[$line->slug] ?? $defaultLineImage;
            @endphp
            <div id="{{ $line->slug }}" class="mb-12 last:mb-0 scroll-mt-28 service-line-target">
                {{-- Compact section header instead of a full-width banner --}}
                <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                    <img src="{{ asset($lineImage['image']) }}" alt="{{ $lineImage['alt'] }}"
                        class="hidden sm:block w-28 h-20 rounded-xl object-cover shadow-md ring-1 ring-black/5 flex-shrink-0" loading="lazy" />
                    <div class="min-w-0">
                        <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 uppercase tracking-tight">{{ $line->name }}</h2>
                        <p class="text-gray-500 text-sm">{{ $line->description }}</p>
                    </div>
                </div>

                {{-- Horizontal cards: image left, details right, 2-up on desktop --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @forelse($groupPackages as $package)
                        @php $meta = $packageImages[$package->slug] ?? $defaultPackageImage; @endphp
                        <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col {{ $package->is_featured ? 'ring-2 ring-yellow-400 border-transparent' : '' }}">
                            @if($package->is_featured)
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow">Recommended</span>
                                </div>
                            @endif

                            <img src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="w-full h-36 object-cover" loading="lazy">

                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-900 mb-1">{{ $package->name }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 flex-1">{{ $package->description }}</p>

                                <div class="flex items-baseline gap-2 mt-3">
                                    <span class="text-xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                    <span class="text-xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] text-gray-500 mt-0.5">
                                    <svg class="w-3 h-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Delivery in {{ $package->delivery_days }} business days
                                </div>

                                <div class="flex items-center gap-2 w-full mt-4">
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-outline flex-1 justify-center py-2.5 text-xs">Enquire</a>
                                    <a href="{{ route('payment.checkout', ['type' => 'package', 'id' => $package->id]) }}" class="btn-accent flex-1 justify-center py-2.5 text-xs">Pay Now →</a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm">Packages for this service line are being prepared — contact us for a custom quote.</p>
                    @endforelse
                </div>
            </div>
        @endforeach

        @if($uncategorised->isNotEmpty())
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-5 pb-5 border-b border-gray-100">
                    <span class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <h2 class="text-xl md:text-2xl font-extrabold text-gray-900 uppercase tracking-tight">Other Packages</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach($uncategorised as $package)
                        @php $meta = $packageImages[$package->slug] ?? $defaultPackageImage; @endphp
                        <div class="group relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden flex flex-col {{ $package->is_featured ? 'ring-2 ring-yellow-400 border-transparent' : '' }}">
                            @if($package->is_featured)
                                <div class="absolute top-3 right-3 z-10">
                                    <span class="bg-gradient-to-r from-yellow-600 to-yellow-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow">Recommended</span>
                                </div>
                            @endif

                            <img src="{{ asset($meta['image']) }}" alt="{{ $meta['alt'] }}" class="w-full h-36 object-cover" loading="lazy">

                            <div class="p-5 flex flex-col flex-1">
                                <h3 class="text-base font-bold text-gray-900 mb-1">{{ $package->name }}</h3>
                                <p class="text-gray-500 text-sm leading-relaxed line-clamp-2 flex-1">{{ $package->description }}</p>

                                <div class="flex items-baseline gap-2 mt-3">
                                    <span class="text-xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($package->price_tzs) }}</span>
                                    <span class="text-xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($package->price_usd) }}</span>
                                </div>
                                <div class="flex items-center gap-1 text-[11px] text-gray-500 mt-0.5">
                                    <svg class="w-3 h-3 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Delivery in {{ $package->delivery_days }} business days
                                </div>

                                <div class="flex items-center gap-2 w-full mt-4">
                                    <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-outline flex-1 justify-center py-2.5 text-xs">Enquire</a>
                                    <a href="{{ route('payment.checkout', ['type' => 'package', 'id' => $package->id]) }}" class="btn-accent flex-1 justify-center py-2.5 text-xs">Pay Now →</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Care Plans --}}
        <div class="mb-12">
            <div class="relative rounded-2xl overflow-hidden mb-4 shadow-lg">
                <img src="{{ asset('images/hero/scanner-analytics.jpg') }}" alt="Analyst monitoring website performance charts on a laptop"
                    class="h-48 md:h-64 w-full object-cover transition-transform duration-500 hover:scale-[1.03]" loading="lazy" />
                <div class="absolute bottom-0 inset-x-0 bg-black/90 border-t-2 border-yellow-500 px-5 py-3 text-on-photo">
                    <h2 class="text-xl md:text-2xl font-bold text-white">Monthly Care Plans</h2>
                    <p class="text-gray-300 text-sm">We keep your systems updated, secure and fast — so you never think about it.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                @forelse($carePlans as $plan)
                    <div class="relative bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-6 text-center flex flex-col items-center {{ $plan->is_featured ? 'ring-2 ring-yellow-400 border-transparent' : '' }}">
                        @if($plan->is_featured)
                            <div class="absolute top-3 right-3">
                                <span class="bg-yellow-500 text-black text-[10px] font-bold px-3 py-1 rounded-full shadow">Best Value</span>
                            </div>
                        @endif

                        <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mb-3">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>

                        <h3 class="text-lg font-bold text-gray-900 mb-1">{{ $plan->name }}</h3>
                        <p class="text-gray-500 text-sm leading-relaxed flex-1">{{ $plan->description }}</p>

                        <div class="flex items-baseline justify-center gap-1 mt-4">
                            <span class="text-2xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'inline' : 'none' }}">TZS {{ number_format($plan->price_tzs) }}</span>
                            <span class="text-2xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'inline' : 'none' }}">${{ number_format($plan->price_usd) }}</span>
                            <span class="text-xs text-gray-500">/month</span>
                        </div>

                        <div class="flex items-center gap-2 w-full mt-4">
                            <a href="{{ route('enquiry.create', ['package' => $plan->slug]) }}" class="btn-outline flex-1 justify-center py-2.5 text-xs">Enquire</a>
                            <a href="{{ route('payment.checkout', ['type' => 'care-plan', 'id' => $plan->id]) }}" class="btn-accent flex-1 justify-center py-2.5 text-xs">Pay Now →</a>
                        </div>
                    </div>
                @empty
                    <div class="hidden"></div>
                @endforelse
            </div>
        </div>
    </div>
</section>

@endsection
