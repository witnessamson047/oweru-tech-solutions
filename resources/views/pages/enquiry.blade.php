@extends('layouts.public')

@section('title', 'Get in Touch - Oweru Tech Solutions')

@section('content')

<section class="py-12 lg:py-16 bg-gray-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-black mb-2">Let's Talk About Your Project</h1>
            <p class="text-gray-600">Fill out the form below and our team will get back to you within 24 hours.</p>
            @if($selectedPackage)
                <div class="mt-3 inline-flex items-center gap-2 bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-2 rounded-lg text-sm font-medium">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    Interested in: <strong>{{ $selectedPackage->name }}</strong>
                </div>
            @endif
        </div>

        {{-- Form --}}
        <form id="enquiry-form" action="{{ route('enquiry.store') }}" method="POST" class="card">
            @csrf

            {{-- Hidden package field --}}
            @if($selectedPackage)
                <input type="hidden" name="package_id" value="{{ $selectedPackage->id }}">
                <input type="hidden" name="package_name" value="{{ $selectedPackage->name }}">
            @endif

            <div class="space-y-6">

                {{-- Contact Information --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Contact Information</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="John Doe">
                            @error('name') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="business_name" class="block text-sm font-medium text-gray-700 mb-1">Business Name *</label>
                            <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="Your Business Ltd">
                            @error('business_name') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="john@business.com">
                            @error('email') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="+255 XXX XXX XXX">
                            @error('phone') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country *</label>
                            <select name="country" id="country" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm">
                                <option value="">Select country</option>
                                <option value="Tanzania" {{ old('country') === 'Tanzania' ? 'selected' : '' }}>Tanzania</option>
                                <option value="Kenya" {{ old('country') === 'Kenya' ? 'selected' : '' }}>Kenya</option>
                                <option value="Uganda" {{ old('country') === 'Uganda' ? 'selected' : '' }}>Uganda</option>
                                <option value="Rwanda" {{ old('country') === 'Rwanda' ? 'selected' : '' }}>Rwanda</option>
                                <option value="Burundi" {{ old('country') === 'Burundi' ? 'selected' : '' }}>Burundi</option>
                                <option value="Other" {{ old('country') === 'Other' ? 'selected' : '' }}>Other</option>
                            </select>
                            @error('country') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Project Details --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Project Details</h3>
                    <div class="space-y-4">
                        {{-- Package Interest --}}
                        <div>
                            <label for="package_id" class="block text-sm font-medium text-gray-700 mb-1">Package of Interest</label>
                            <select name="package_id" id="package_id"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm">
                                <option value="">Select a package (optional)</option>
                                @foreach($allPackages as $group => $groupPackages)
                                    <optgroup label="{{ ucfirst($group) }}">
                                        @foreach($groupPackages as $pkg)
                                            <option value="{{ $pkg->id }}" {{ old('package_id', request('package')) == $pkg->slug ? 'selected' : '' }}>
                                                {{ $pkg->name }} - TZS {{ number_format($pkg->price_tzs) }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                        </div>

                        {{-- Problem Description --}}
                        <div>
                            <label for="problem_description" class="block text-sm font-medium text-gray-700 mb-1">Tell us about your project or challenge *</label>
                            <textarea name="problem_description" id="problem_description" rows="4" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="Describe what you need, any problems you're facing, or what you'd like to achieve...">{{ old('problem_description') }}</textarea>
                            @error('problem_description') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Current Cost / Impact --}}
                        <div>
                            <label for="current_cost" class="block text-sm font-medium text-gray-700 mb-1">Current cost or impact of this problem</label>
                            <input type="text" name="current_cost" id="current_cost" value="{{ old('current_cost') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="e.g., Losing ~50 customers/month, TZS 2M/year in manual work">
                        </div>

                        {{-- Required Date --}}
                        <div>
                            <label for="required_date" class="block text-sm font-medium text-gray-700 mb-1">When do you need this completed?</label>
                            <input type="date" name="required_date" id="required_date" value="{{ old('required_date') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm">
                        </div>
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Budget --}}
                <div>
                    <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Budget</h3>
                    <div>
                        <label for="budget_range" class="block text-sm font-medium text-gray-700 mb-1">What is your budget range? *</label>
                        <select name="budget_range" id="budget_range" required
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm">
                            <option value="">Select a range</option>
                            <option value="under_500k" {{ old('budget_range') === 'under_500k' ? 'selected' : '' }}>Under TZS 500,000</option>
                            <option value="500k_1m" {{ old('budget_range') === '500k_1m' ? 'selected' : '' }}>TZS 500,000 - 1,000,000</option>
                            <option value="1m_5m" {{ old('budget_range') === '1m_5m' ? 'selected' : '' }}>TZS 1,000,000 - 5,000,000</option>
                            <option value="5m_10m" {{ old('budget_range') === '5m_10m' ? 'selected' : '' }}>TZS 5,000,000 - 10,000,000</option>
                            <option value="over_10m" {{ old('budget_range') === 'over_10m' ? 'selected' : '' }}>Over TZS 10,000,000</option>
                            <option value="prefer_not_say" {{ old('budget_range') === 'prefer_not_say' ? 'selected' : '' }}>Prefer not to say</option>
                        </select>
                        @error('budget_range') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <hr class="border-gray-200">

                {{-- Consent --}}
                <div class="flex items-start gap-3">
                    <input type="checkbox" name="consent" id="consent" required
                        class="mt-1 h-4 w-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                    <label for="consent" class="text-sm text-gray-600">
                        I agree to Oweru International Ltd processing my data for the purpose of this enquiry.
                        I have read and agree to the <a href="#" class="text-yellow-600 hover:underline">Privacy Policy</a>.
                    </label>
                </div>
                @error('consent') <p class="text-black text-xs mt-1 ml-7">{{ $message }}</p> @enderror

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit" class="btn-primary w-full justify-center text-base py-3">
                        Submit Enquiry →
                    </button>
                    <p class="text-center text-xs text-gray-400 mt-3">We typically respond within 24 business hours.</p>
                </div>
            </div>
        </form>
    </div>
</section>

@endsection
