@extends('layouts.admin')

@section('title', __('auth.password_change.title'))
@section('page-title', __('auth.password_change.title'))
@section('page-subtitle', __('auth.password_change.subtitle'))

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="admin-card p-5 sm:p-7">
        <div class="mb-6">
            <h2 class="text-base font-semibold text-gray-900">{{ __('auth.password_change.title') }}</h2>
            <p class="mt-1 text-sm text-gray-500">{{ __('auth.password_change.subtitle') }}</p>
        </div>

        <form method="POST" action="{{ route('admin.account.password.update') }}" class="space-y-5">
            @csrf
            @method('PATCH')

            <div>
                <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.password_change.current') }}</label>
                <div class="relative">
                    <input type="password" id="current_password" name="current_password" autocomplete="current-password" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-16 text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                    <button type="button" class="password-visibility absolute inset-y-0 right-3 text-xs font-semibold text-gray-600 hover:text-gray-900"
                        data-target="current_password" data-show-label="{{ __('auth.login.show_password') }}" data-hide-label="{{ __('auth.login.hide_password') }}"
                        aria-label="{{ __('auth.login.show_password') }}" aria-pressed="false">{{ __('auth.login.show_password') }}</button>
                </div>
                @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.password_change.new') }}</label>
                <div class="relative">
                    <input type="password" id="password" name="password" autocomplete="new-password" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-16 text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                    <button type="button" class="password-visibility absolute inset-y-0 right-3 text-xs font-semibold text-gray-600 hover:text-gray-900"
                        data-target="password" data-show-label="{{ __('auth.login.show_password') }}" data-hide-label="{{ __('auth.login.hide_password') }}"
                        aria-label="{{ __('auth.login.show_password') }}" aria-pressed="false">{{ __('auth.login.show_password') }}</button>
                </div>
                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">{{ __('auth.password_change.confirm') }}</label>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required
                        class="w-full rounded-lg border border-gray-300 px-3 py-2.5 pr-16 text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500">
                    <button type="button" class="password-visibility absolute inset-y-0 right-3 text-xs font-semibold text-gray-600 hover:text-gray-900"
                        data-target="password_confirmation" data-show-label="{{ __('auth.login.show_password') }}" data-hide-label="{{ __('auth.login.hide_password') }}"
                        aria-label="{{ __('auth.login.show_password') }}" aria-pressed="false">{{ __('auth.login.show_password') }}</button>
                </div>
            </div>

            <p class="text-xs text-gray-500">{{ __('auth.password_change.requirements') }}</p>

            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-5">
                <button type="submit" class="admin-btn-gold px-4 py-2.5 text-sm">{{ __('auth.password_change.submit') }}</button>
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">{{ __('auth.password_change.cancel') }}</a>
            </div>
        </form>
    </div>
</div>
<script>
    document.querySelectorAll('.password-visibility').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.target);
            const isVisible = input.type === 'text';
            input.type = isVisible ? 'password' : 'text';
            const label = isVisible ? button.dataset.showLabel : button.dataset.hideLabel;
            button.textContent = label;
            button.setAttribute('aria-label', label);
            button.setAttribute('aria-pressed', String(!isVisible));
        });
    });
</script>
@endsection