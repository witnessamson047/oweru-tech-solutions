@extends('layouts.public')

@section('title', __('contact.header.badge') . ' - Oweru Tech Solutions')

@section('content')

<section class="contact-hero">
    <div class="contact-hero-inner">
        <div class="contact-hero-copy">
            <span class="contact-kicker">{{ __('contact.header.badge') }}</span>
            <h1>{{ __('contact.header.title') }}</h1>
            <p>{{ __('contact.header.lead') }}</p>
            <a href="#contact-form" class="contact-hero-link">Start a conversation <span aria-hidden="true">&rarr;</span></a>
        </div>
    </div>
</section>

<section class="contact-content">
    <div class="contact-content-inner">
        <div class="contact-layout">

            {{-- Form — deliberately light: name, email, message --}}
            <div class="contact-form-column">
                <div class="contact-section-heading">
                    <span>01 / Send a message</span>
                    <h2>Tell us what you have in mind.</h2>
                </div>
                <form id="contact-form" action="{{ route('contact.store') }}" method="POST" class="contact-form">
                    @csrf

                    {{-- Honeypot (hidden from humans) --}}
                    <input type="text" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                    <div class="contact-form-fields">
                        <div class="contact-field">
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.form.name') }} {{ __('contact.form.required') }}</label>
                            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="{{ __('contact.form.name_placeholder') }}">
                            @error('name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="contact-field">
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.form.email') }} {{ __('contact.form.required') }}</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="{{ __('contact.form.email_placeholder') }}">
                            @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="contact-field contact-field-wide">
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.form.phone') }} <span class="text-gray-400 font-normal">{{ __('contact.form.phone_optional') }}</span></label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="{{ __('contact.form.phone_placeholder') }}">
                            @error('phone') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="contact-field contact-field-wide">
                            <label for="message" class="block text-sm font-medium text-gray-700 mb-1">{{ __('contact.form.message') }} {{ __('contact.form.required') }}</label>
                            <textarea name="message" id="message" rows="5" required maxlength="2000"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500 text-sm"
                                placeholder="{{ __('contact.form.message_placeholder') }}">{{ old('message') }}</textarea>
                            @error('message') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="contact-consent contact-field-wide">
                            <input type="checkbox" name="consent" id="consent" required
                                class="mt-1 h-4 w-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                            <label for="consent" class="text-sm text-gray-600">
                                {{ __('contact.form.consent') }}
                            </label>
                        </div>
                        @error('consent') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror

                        <button type="submit" class="contact-submit contact-field-wide">{{ __('contact.form.send') }} <span aria-hidden="true">&rarr;</span></button>
                        <p class="contact-form-note contact-field-wide">{{ __('contact.form.have_project') }} <a href="{{ route('enquiry.create') }}">{{ __('contact.form.have_project_link') }}</a> {{ __('contact.form.have_project_after') }}</p>
                    </div>
                </form>
            </div>

            {{-- Direct contact info --}}
            <aside class="contact-direct">
                <div class="contact-section-heading">
                    <span>02 / Direct contact</span>
                    <h2>{{ __('contact.direct.title') }}</h2>
                </div>
                <ul class="contact-details">
                        <li>
                            <span class="contact-detail-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </span>
                            <div class="contact-detail-copy">
                                <p class="text-xs text-gray-500">{{ __('contact.direct.email') }}</p>
                                <a href="mailto:inf@oweru.com" class="text-sm font-medium text-gray-900 hover:underline">inf@oweru.com</a>
                            </div>
                        </li>
                        <li>
                            <span class="contact-detail-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </span>
                            <div class="contact-detail-copy">
                                <p class="text-xs text-gray-500">{{ __('contact.direct.phone') }}</p>
                                <a href="tel:+255711890764" class="text-sm font-medium text-gray-900 hover:underline">+255 711 890 764</a>
                            </div>
                        </li>
                        <li>
                            <span class="contact-detail-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <div class="contact-detail-copy">
                                <p class="text-xs text-gray-500">{{ __('contact.direct.location') }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ __('contact.direct.location_value') }}</p>
                            </div>
                        </li>
                        <li>
                            <span class="contact-detail-icon">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </span>
                            <div class="contact-detail-copy">
                                <p class="text-xs text-gray-500">{{ __('contact.direct.hours') }}</p>
                                <p class="text-sm font-medium text-gray-900">{{ __('contact.direct.hours_value') }}</p>
                            </div>
                        </li>
                </ul>

                    <a href="https://wa.me/255711890764?text={{ rawurlencode('Hello Oweru, I have a question.') }}"
                        target="_blank" rel="noopener noreferrer" class="contact-whatsapp">
                        <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        {{ __('contact.direct.whatsapp') }}
                    </a>
            </aside>

        </div>
    </div>
</section>

<style>
    .contact-hero {
        background: #142d28;
        color: #fff;
    }

    .contact-hero-inner,
    .contact-content-inner {
        width: min(1120px, calc(100% - 40px));
        margin: 0 auto;
    }

    .contact-hero-inner {
        padding: 22px 0 20px;
        text-align: center;
    }

    .contact-hero-copy {
        max-width: 760px;
        margin: 0 auto;
    }

    .contact-kicker,
    .contact-section-heading > span {
        color: #d4a943;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
    }

    .contact-hero-copy h1 {
        margin: 10px 0 0;
        color: #fff;
        font-size: 36px;
        font-weight: 750;
        line-height: 1.04;
    }

    .contact-hero-copy p {
        max-width: 470px;
        margin: 10px auto 0;
        color: #d6e0dc;
        font-size: 1rem;
        line-height: 1.7;
    }

    .contact-hero-link {
        display: inline-flex;
        gap: 14px;
        align-items: center;
        margin-top: 12px;
        color: #f2c55e;
        font-size: 0.9rem;
        font-weight: 700;
        text-decoration: none;
    }

    .contact-hero-link span {
        font-size: 1.2rem;
        transition: transform 160ms ease;
    }

    .contact-hero-link:hover span {
        transform: translateX(4px);
    }

    .contact-content {
        padding: 58px 0 72px;
        background: #f6f7f4;
    }

    .contact-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.45fr) minmax(250px, 0.75fr);
        gap: 72px;
        align-items: start;
    }

    .contact-section-heading {
        margin-bottom: 22px;
    }

    .contact-section-heading h2 {
        margin: 8px 0 0;
        color: #192a25;
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.25;
    }

    .contact-form {
        padding: 26px;
        border: 1px solid #e2e6e1;
        border-radius: 5px;
        background: #fff;
    }

    .contact-form-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px 16px;
    }

    .contact-field-wide {
        grid-column: 1 / -1;
    }

    .contact-field label {
        display: block;
        margin-bottom: 6px;
        color: #34433d;
        font-size: 0.8rem;
        font-weight: 650;
    }

    .contact-field input,
    .contact-field textarea {
        display: block;
        width: 100%;
        border: 1px solid #d6ddd7;
        border-radius: 4px;
        background: #fcfdfb;
        padding: 11px 12px;
        color: #17211d;
        font: inherit;
        font-size: 0.88rem;
    }

    .contact-field textarea {
        min-height: 132px;
        resize: vertical;
    }

    .contact-field input:focus,
    .contact-field textarea:focus {
        outline: 2px solid rgba(202, 157, 53, 0.35);
        border-color: #b98720;
    }

    .contact-consent {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        color: #59665f;
        font-size: 0.78rem;
        line-height: 1.5;
    }

    .contact-consent input {
        flex: 0 0 auto;
        margin-top: 3px;
        accent-color: #b98720;
    }

    .contact-submit {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 0;
        border-radius: 4px;
        background: #183b32;
        padding: 13px 16px;
        color: #fff;
        font: inherit;
        font-size: 0.9rem;
        font-weight: 700;
        cursor: pointer;
    }

    .contact-submit:hover {
        background: #245448;
    }

    .contact-form-note {
        margin: -5px 0 0;
        color: #78827d;
        font-size: 0.72rem;
        text-align: center;
    }

    .contact-form-note a {
        color: #385e51;
        font-weight: 650;
    }

    .contact-direct {
        padding-top: 2px;
    }

    .contact-details {
        margin: 0;
        padding: 0;
        list-style: none;
    }

    .contact-details li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 15px 0;
        border-bottom: 1px solid #dfe4df;
    }

    .contact-detail-icon {
        display: grid;
        width: 34px;
        height: 34px;
        flex: 0 0 auto;
        place-items: center;
        border-radius: 4px;
        background: #e8eee8;
        color: #385e51;
    }

    .contact-detail-icon svg {
        width: 16px;
        height: 16px;
    }

    .contact-detail-copy p {
        margin: 0;
    }

    .contact-detail-copy p:first-child {
        margin-bottom: 3px;
        color: #7b857f;
        font-size: 0.7rem;
    }

    .contact-detail-copy p:last-child,
    .contact-detail-copy a {
        color: #26352e;
        font-size: 0.84rem;
        font-weight: 650;
        overflow-wrap: anywhere;
    }

    .contact-detail-copy a {
        text-decoration: none;
    }

    .contact-detail-copy a:hover {
        text-decoration: underline;
    }

    .contact-whatsapp {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        margin-top: 22px;
        border: 1px solid #b9c9be;
        border-radius: 4px;
        padding: 12px;
        color: #244c3c;
        font-size: 0.84rem;
        font-weight: 700;
        text-decoration: none;
    }

    .contact-whatsapp:hover {
        background: #eaf0e9;
    }

    @media (max-width: 760px) {
        .contact-hero-inner {
            padding: 20px 0 18px;
        }

        .contact-hero-copy h1 {
            font-size: 34px;
        }

        .contact-content {
            padding: 40px 0 52px;
        }

        .contact-layout {
            grid-template-columns: 1fr;
            gap: 38px;
        }
    }

    @media (max-width: 520px) {
        .contact-hero-inner,
        .contact-content-inner {
            width: min(100% - 32px, 1120px);
        }

        .contact-form {
            padding: 18px 15px;
        }

        .contact-form-fields {
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .contact-field-wide {
            grid-column: auto;
        }
    }
</style>

@endsection
