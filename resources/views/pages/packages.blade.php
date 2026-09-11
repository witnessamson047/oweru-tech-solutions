@extends('layouts.public')

@section('title', 'Services & Pricing - Oweru Tech Solutions')

@section('content')

{{-- Page Header --}}
<section class="relative bg-black text-white py-16 overflow-hidden">
    <img src="{{ asset('images/services/header-consultation.jpg') }}" alt="Oweru team reviewing a client project together"
        class="absolute inset-0 h-full w-full object-cover opacity-30" loading="eager" />
    <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/70 to-black"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative">
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
        @endphp

        @foreach($serviceLines as $line)
            @php
                $groupPackages = $packages->where('service_line_id', $line->id);
            @endphp
            @php
                $lineImage = $lineImages[$line->slug] ?? $defaultLineImage;
            @endphp
            <div class="mb-12">
                <div class="relative h-44 md:h-56 rounded-2xl overflow-hidden mb-6 shadow-lg">
                    <img src="{{ asset($lineImage['image']) }}" alt="{{ $lineImage['alt'] }}"
                        class="h-full w-full object-cover transition-transform duration-500 hover:scale-[1.03]" loading="lazy" />
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                    <div class="absolute bottom-4 left-5 right-5">
                        <h2 class="text-2xl font-bold text-white mb-1">{{ $line->name }}</h2>
                        <p class="text-gray-200 text-sm max-w-3xl">{{ $line->description }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($groupPackages as $package)
                        <div class="card {{ $package->is_featured ? 'card-featured' : '' }} hover:shadow-lg transition-all">
                            @if($package->is_featured)
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-yellow-500 text-black text-xs font-bold px-4 py-1 rounded-full">Recommended</div>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $package->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ $package->description }}</p>
                            <div class="mb-4">
                                <span class="text-3xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'block' : 'none' }}">
                                    TZS {{ number_format($package->price_tzs) }}
                                </span>
                                <span class="text-3xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'block' : 'none' }}">
                                    ${{ number_format($package->price_usd) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 mb-6">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $package->delivery_days }} business days
                                </span>
                            </div>
                            <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-primary w-full justify-center">
                                Enquire Now →
                            </a>
                        </div>
                    @empty
                        <p class="text-gray-400 text-sm col-span-full">Packages for this service line are being prepared — contact us for a custom quote.</p>
                    @endforelse
                </div>
            </div>
        @endforeach

        @if($uncategorised->isNotEmpty())
            <div class="mb-12">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    </span>
                    <h2 class="text-2xl font-bold text-black">Other Packages</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($uncategorised as $package)
                        <div class="card {{ $package->is_featured ? 'card-featured' : '' }} hover:shadow-lg transition-all">
                            @if($package->is_featured)
                                <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-yellow-500 text-black text-xs font-bold px-4 py-1 rounded-full">Recommended</div>
                            @endif
                            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $package->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ $package->description }}</p>
                            <div class="mb-4">
                                <span class="text-3xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'block' : 'none' }}">
                                    TZS {{ number_format($package->price_tzs) }}
                                </span>
                                <span class="text-3xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'block' : 'none' }}">
                                    ${{ number_format($package->price_usd) }}
                                </span>
                            </div>
                            <div class="text-sm text-gray-500 mb-6">{{ $package->delivery_days }} business days</div>
                            <a href="{{ route('enquiry.create', ['package' => $package->slug]) }}" class="btn-primary w-full justify-center">Enquire Now →</a>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Care Plans --}}
        <div class="mb-12">
            <div class="relative h-44 md:h-56 rounded-2xl overflow-hidden mb-6 shadow-lg">
                <img src="{{ asset('images/hero/scanner-analytics.jpg') }}" alt="Analyst monitoring website performance charts on a laptop"
                    class="h-full w-full object-cover transition-transform duration-500 hover:scale-[1.03]" loading="lazy" />
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/30 to-transparent"></div>
                <div class="absolute bottom-4 left-5 right-5">
                    <h2 class="text-2xl font-bold text-white mb-1">Monthly Care Plans</h2>
                    <p class="text-gray-200 text-sm max-w-3xl">We keep your systems updated, secure and fast — so you never think about it.</p>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @forelse($carePlans as $plan)
                    <div class="card {{ $plan->is_featured ? 'card-featured' : '' }} text-center hover:shadow-lg transition-all">
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $plan->name }}</h3>
                        <p class="text-gray-600 text-sm mb-4">{{ $plan->description }}</p>
                        <div class="mb-4">
                            <span class="text-2xl font-extrabold text-gray-900 price-tzs" style="display: {{ !$currency || $currency === 'TZS' ? 'block' : 'none' }}">
                                TZS {{ number_format($plan->price_tzs) }}
                            </span>
                            <span class="text-2xl font-extrabold text-gray-900 price-usd" style="display: {{ $currency === 'USD' ? 'block' : 'none' }}">
                                ${{ number_format($plan->price_usd) }}
                            </span>
                            <span class="text-sm text-gray-500">/month</span>
                        </div>
                        <a href="{{ route('enquiry.create', ['package' => $plan->slug]) }}" class="btn-outline w-full justify-center">
                            Subscribe →
                        </a>
                    </div>
                @empty
                    <div class="hidden"></div>
                @endforelse
            </div>
        </div>
    </div>
</section>

@endsection
