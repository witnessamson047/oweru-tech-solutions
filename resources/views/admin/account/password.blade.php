@extends('layouts.admin')

@section('title', __('auth.password_change.title'))
@section('page-title', __('auth.password_change.title'))
@section('page-subtitle', __('auth.password_change.subtitle'))

@section('content')

<div class="max-w-2xl">
    <x-admin.card icon="key"
                  :title="__('auth.password_change.title')"
                  :subtitle="__('auth.password_change.subtitle')">
        <form method="POST" action="{{ route('admin.account.password.update') }}" class="space-y-4">
            @csrf
            @method('PATCH')

            <x-admin.field name="current_password" :label="__('auth.password_change.current')" required>
                <div class="relative">
                    <input type="password" id="current_password" name="current_password"
                           autocomplete="current-password" required class="!pr-16">
                    <button type="button"
                            class="password-visibility absolute inset-y-0 right-3 text-xs font-semibold text-gray-600 hover:text-gray-900"
                            data-target="current_password"
                            data-show-label="{{ __('auth.login.show_password') }}"
                            data-hide-label="{{ __('auth.login.hide_password') }}"
                            aria-label="{{ __('auth.login.show_password') }}"
                            aria-pressed="false">{{ __('auth.login.show_password') }}</button>
                </div>
            </x-admin.field>

            <x-admin.field name="password" :label="__('auth.password_change.new')" required>
                <div class="relative">
                    <input type="password" id="password" name="password"
                           autocomplete="new-password" required class="!pr-16">
                    <button type="button"
                            class="password-visibility absolute inset-y-0 right-3 text-xs font-semibold text-gray-600 hover:text-gray-900"
                            data-target="password"
                            data-show-label="{{ __('auth.login.show_password') }}"
                            data-hide-label="{{ __('auth.login.hide_password') }}"
                            aria-label="{{ __('auth.login.show_password') }}"
                            aria-pressed="false">{{ __('auth.login.show_password') }}</button>
                </div>
            </x-admin.field>

            <x-admin.field name="password_confirmation" :label="__('auth.password_change.confirm')" required>
                <div class="relative">
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           autocomplete="new-password" required class="!pr-16">
                    <button type="button"
                            class="password-visibility absolute inset-y-0 right-3 text-xs font-semibold text-gray-600 hover:text-gray-900"
                            data-target="password_confirmation"
                            data-show-label="{{ __('auth.login.show_password') }}"
                            data-hide-label="{{ __('auth.login.hide_password') }}"
                            aria-label="{{ __('auth.login.show_password') }}"
                            aria-pressed="false">{{ __('auth.login.show_password') }}</button>
                </div>
            </x-admin.field>

            <p class="admin-hint">{{ __('auth.password_change.requirements') }}</p>

            <div class="flex flex-wrap items-center gap-3 border-t pt-5" style="border-color: var(--admin-border)">
                <button type="submit" class="admin-btn-gold">
                    <x-admin.icon name="check" class="w-4 h-4" /> {{ __('auth.password_change.submit') }}
                </button>
                <a href="{{ route('admin.dashboard') }}" class="admin-inline-link text-sm">{{ __('auth.password_change.cancel') }}</a>
            </div>
        </form>
    </x-admin.card>
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