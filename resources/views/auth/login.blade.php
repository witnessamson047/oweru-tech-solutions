@extends('layouts.public')

@section('title', __('auth.login.title'))

@section('content')
<section class="admin-login">
    <div class="admin-login-layout">
        <div class="admin-login-intro">
            <a href="{{ route('home') }}" class="admin-login-brand" aria-label="Oweru International Ltd home">
                <img src="{{ asset('images/brand/main-2.svg') }}" alt="{{ __('auth.login.brand_alt') }}">
            </a>
            <span class="admin-login-kicker">{{ __('auth.login.workspace') }}</span>
            <h1>{{ __('auth.login.headline_first') }}<br>{{ __('auth.login.headline_second') }}</h1>
            <p>{{ __('auth.login.lead') }}</p>

            <ul class="admin-login-modules" aria-label="Workspace modules">
                <li>{{ __('auth.login.module_enquiries') }}</li>
                <li>{{ __('auth.login.module_scans') }}</li>
                <li>{{ __('auth.login.module_invoices') }}</li>
            </ul>
            <span class="admin-login-security">{{ __('auth.login.access_notice') }}</span>
        </div>

        <div class="admin-login-panel">
            <div class="admin-login-heading">
                <span>{{ __('auth.login.welcome') }}</span>
                <h2>{{ __('auth.login.heading') }}</h2>
                <p>{{ __('auth.login.intro') }}</p>
            </div>

            <form method="POST" action="{{ route('login') }}" class="admin-login-form">
                @csrf
                <div class="admin-login-field">
                    <label for="email">{{ __('auth.login.email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}"
                        autocomplete="username" placeholder="{{ __('auth.login.email_placeholder') }}" required autofocus>
                    @error('email') <p class="admin-login-error">{{ $message }}</p> @enderror
                </div>

                <div class="admin-login-field">
                    <label for="password">{{ __('auth.login.password') }}</label>
                    <div class="admin-password-field">
                        <input type="password" name="password" id="password"
                            autocomplete="current-password" placeholder="{{ __('auth.login.password_placeholder') }}" required>
                        <button type="button" id="password-visibility"
                            data-show-label="{{ __('auth.login.show_password') }}"
                            data-hide-label="{{ __('auth.login.hide_password') }}"
                            aria-label="{{ __('auth.login.show_password') }}" aria-pressed="false">{{ __('auth.login.show_password') }}</button>
                    </div>
                    @error('password') <p class="admin-login-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="admin-login-submit">{{ __('auth.login.submit') }} <span aria-hidden="true">&rarr;</span></button>
            </form>

            <p class="admin-login-support">{{ __('auth.login.register_prompt') }} <a href="{{ route('register') }}">{{ __('auth.login.register_link') }}</a></p>
        </div>
    </div>
</section>

<style>
    .admin-login {
        padding: 40px 20px;
        background: #f1f4f0;
    }

    .admin-login-layout {
        display: grid;
        width: min(100%, 1000px);
        min-height: 520px;
        grid-template-columns: 1fr 0.92fr;
        margin: 0 auto;
        overflow: hidden;
        border: 1px solid #dfe5df;
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 16px 40px rgba(24, 50, 40, 0.08);
    }

    .admin-login-intro {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        background: #17372f;
        padding: 42px;
        color: #fff;
    }

    .admin-login-brand {
        display: inline-flex;
        align-items: center;
        min-height: 40px;
        margin-bottom: 52px;
    }

    .admin-login-brand img {
        display: block;
        width: auto;
        height: 38px;
    }

    .admin-login-kicker {
        color: #e7c15d;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .admin-login-intro h1 {
        margin: 12px 0 0;
        color: #fff;
        font-size: 38px;
        font-weight: 750;
        line-height: 1.1;
    }

    .admin-login-intro > p {
        max-width: 360px;
        margin: 14px 0 0;
        color: #d0ddd7;
        font-size: 0.9rem;
        line-height: 1.7;
    }

    .admin-login-modules {
        display: grid;
        gap: 10px;
        margin: 26px 0 30px;
        padding: 0;
        list-style: none;
        color: #edf2ee;
        font-size: 0.8rem;
    }

    .admin-login-modules li::before {
        content: "";
        display: inline-block;
        width: 6px;
        height: 6px;
        margin: 0 10px 2px 0;
        border-radius: 50%;
        background: #e7c15d;
    }

    .admin-login-security {
        margin-top: auto;
        border-top: 1px solid rgba(255,255,255,0.18);
        padding-top: 14px;
        color: #b8c9c0;
        font-size: 0.7rem;
    }

    .admin-login-panel {
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: 44px;
    }

    .admin-login-heading > span {
        color: #9a7523;
        font-size: 0.76rem;
        font-weight: 700;
    }

    .admin-login-heading h2 {
        margin: 9px 0 0;
        color: #1d3028;
        font-size: 25px;
        font-weight: 750;
        line-height: 1.25;
    }

    .admin-login-heading p {
        margin: 9px 0 0;
        color: #6d7972;
        font-size: 0.82rem;
        line-height: 1.55;
    }

    .admin-login-form {
        display: grid;
        gap: 20px;
        margin-top: 28px;
    }

    .admin-login-field label {
        display: block;
        margin-bottom: 7px;
        color: #35443c;
        font-size: 0.78rem;
        font-weight: 650;
    }

    .admin-login-field input {
        width: 100%;
        min-height: 44px;
        border: 1px solid #d4ddd6;
        border-radius: 4px;
        background: #fff;
        padding: 10px 12px;
        color: #1d3028;
        font: inherit;
        font-size: 0.84rem;
    }

    .admin-login-field input:focus {
        outline: 2px solid rgba(213, 173, 69, 0.3);
        border-color: #b98a20;
    }

    .admin-password-field {
        position: relative;
    }

    .admin-password-field input {
        padding-right: 76px;
    }

    .admin-password-field button {
        position: absolute;
        top: 50%;
        right: 10px;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        padding: 6px;
        color: #456a59;
        font: inherit;
        font-size: 0.72rem;
        font-weight: 700;
        cursor: pointer;
    }

    .admin-login-error {
        margin: 6px 0 0;
        color: #b42318;
        font-size: 0.72rem;
    }

    .admin-login-submit {
        display: flex;
        min-height: 46px;
        align-items: center;
        justify-content: space-between;
        border: 0;
        border-radius: 4px;
        background: #e8bd50;
        padding: 0 15px;
        color: #17251f;
        font: inherit;
        font-size: 0.86rem;
        font-weight: 750;
        cursor: pointer;
    }

    .admin-login-submit:hover {
        background: #f1ce70;
    }

    .admin-login-support {
        margin: 22px 0 0;
        color: #7b857e;
        font-size: 0.74rem;
    }

    .admin-login-support a {
        color: #315b49;
        font-weight: 700;
        text-decoration: none;
    }

    .admin-login-support a:hover {
        text-decoration: underline;
    }

    @media (max-width: 680px) {
        .admin-login {
            padding: 20px 14px;
        }

        .admin-login-layout {
            min-height: 0;
            grid-template-columns: 1fr;
        }

        .admin-login-intro {
            padding: 24px;
        }

        .admin-login-brand {
            margin-bottom: 24px;
        }

        .admin-login-intro h1 {
            font-size: 32px;
        }

        .admin-login-modules {
            gap: 7px;
            margin: 18px 0;
        }

        .admin-login-security {
            margin-top: 0;
        }

        .admin-login-panel {
            padding: 26px 24px;
        }
    }
</style>

<script>
    document.getElementById('password-visibility')?.addEventListener('click', function () {
        const password = document.getElementById('password');
        const isVisible = password.type === 'text';
        password.type = isVisible ? 'password' : 'text';
        const label = isVisible ? this.dataset.showLabel : this.dataset.hideLabel;
        this.textContent = label;
        this.setAttribute('aria-label', label);
        this.setAttribute('aria-pressed', String(!isVisible));
    });
</script>
@endsection
