@extends('layouts.public')

@section('title', __('auth.register.title'))

@section('content')
<section class="min-h-[680px] bg-gray-100 px-4 py-10 sm:py-14">
    <div class="mx-auto max-w-lg rounded-lg border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
        <a href="{{ route('home') }}" class="mb-7 inline-flex" aria-label="{{ __('auth.login.brand_alt') }}">
            <img src="{{ asset('images/brand/main-2.svg') }}" alt="{{ __('auth.login.brand_alt') }}" class="h-9 w-auto">
        </a>

        <p class="text-xs font-semibold uppercase tracking-wider text-yellow-700">{{ __('auth.register.kicker') }}</p>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">{{ __('auth.register.heading') }}</h1>
        <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ __('auth.register.intro') }}</p>

        @if(session('success'))
            <div class="mt-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">
                {{ session('success') }}
            </div>
        @else
            <form method="POST" action="{{ route('register.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.register.name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.register.email') }}</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.register.phone') }}</label>
                    <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="reason" class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.register.reason') }}</label>
                    <textarea id="reason" name="reason" rows="4" required class="w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">{{ old('reason') }}</textarea>
                    @error('reason') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-start gap-2">
                    <input id="consent" name="consent" type="checkbox" value="1" required {{ old('consent') ? 'checked' : '' }} class="mt-1 h-4 w-4 accent-yellow-600">
                    <label for="consent" class="text-xs leading-relaxed text-gray-600">{{ __('auth.register.consent') }}</label>
                </div>
                @error('consent') <p class="text-xs text-red-600">{{ $message }}</p> @enderror

                <button type="submit" class="flex min-h-11 w-full items-center justify-between rounded-lg bg-yellow-500 px-4 py-2.5 text-sm font-semibold text-gray-950 hover:bg-yellow-400">
                    {{ __('auth.register.submit') }} <span aria-hidden="true">&rarr;</span>
                </button>
            </form>
        @endif

        <p class="mt-6 text-sm"><a href="{{ route('login') }}" class="font-medium text-green-800 hover:underline">&larr; {{ __('auth.register.back_to_login') }}</a></p>
    </div>
</section>
@endsection