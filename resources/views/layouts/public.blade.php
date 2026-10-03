<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta-description', 'Oweru International Ltd — digital services, website development and website health scanning.')">
    <title>@yield('title', 'Oweru Tech Solutions')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
@php
    $viteDevServer = env('VITE_DEV_SERVER_URL');
    $manifestPath = public_path('build/manifest.json');
    $useDevServer = !empty($viteDevServer) || !file_exists($manifestPath);

    if (!$useDevServer) {
        $manifest = json_decode(file_get_contents($manifestPath), true);
        $cssFile = $manifest['resources/css/app.css']['file'] ?? '';
        $jsFile = $manifest['resources/js/app.js']['file'] ?? '';
    }
@endphp
@if($useDevServer)
    {{-- Development: Use Vite dev server --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
@else
    {{-- Production: Use built assets --}}
    <link rel="stylesheet" href="/build/{{ $cssFile }}">
    <script type="module" src="/build/{{ $jsFile }}"></script>
@endif
@stack('styles')
</head>
<body class="min-h-screen flex flex-col">

    {{-- ============================================
         NAV — white, thin gold hairline, clear labels
    ============================================ --}}
    <nav id="site-nav" class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-100" style="position: sticky; top: 0; z-index: 1000; background: rgba(255,255,255,0.96) !important; border-bottom: 1px solid var(--gold); box-shadow: 0 1px 0 rgba(15, 23, 42, 0.04);">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="{{ route('home') }}" class="flex items-center shrink-0" aria-label="Oweru International Ltd — Home">
                    <img src="{{ asset('images/brand/main-1.svg') }}" alt="Oweru International Ltd logo" class="h-10 w-auto">
                </a>

                {{-- Desktop links --}}
                <div class="hidden lg:flex items-center gap-5">
                    <a href="{{ route('home') }}" class="text-sm font-medium {{ request()->routeIs('home') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }} transition-colors">{{ __('site.nav.home') }}</a>
                    <a href="{{ route('about') }}" class="text-sm font-medium {{ request()->routeIs('about') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }} transition-colors">{{ __('site.nav.about') }}</a>
                    <a href="{{ route('packages.index') }}" class="text-sm font-medium {{ request()->routeIs('packages.*') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }} transition-colors">{{ __('site.nav.services') }}</a>
                    <a href="{{ route('contact.create') }}" class="text-sm font-medium {{ request()->routeIs('contact.*') ? 'text-gray-900' : 'text-gray-600 hover:text-gray-900' }} transition-colors">{{ __('site.nav.contact') }}</a>
                    <label class="inline-flex items-center">
                        <span class="sr-only">{{ __('site.nav.language') }}</span>
                        <select data-locale-switch aria-label="{{ __('site.nav.language') }}" class="rounded-lg border border-gray-200 bg-white px-2.5 py-1.5 text-xs font-semibold text-gray-700 focus:border-yellow-600 focus:ring-yellow-600">
                            <option value="{{ route('locale.switch', ['locale' => 'en']) }}" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
                            <option value="{{ route('locale.switch', ['locale' => 'sw']) }}" {{ app()->getLocale() === 'sw' ? 'selected' : '' }}>Kiswahili</option>
                        </select>
                    </label>
                    <span class="w-px h-5 bg-gray-200" aria-hidden="true"></span>
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">{{ __('site.nav.dashboard') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">{{ __('site.nav.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900 transition-colors">{{ __('site.nav.login') }}</a>
                    @endauth
                </div>

                {{-- Mobile toggle --}}
                <button id="mobile-menu-btn" type="button"
                    class="lg:hidden inline-flex items-center justify-center w-10 h-10 -mr-2 rounded-lg text-gray-700 hover:bg-gray-100 focus:outline-none"
                    aria-expanded="false" aria-controls="mobile-menu" aria-label="Toggle navigation menu">
                    <svg id="mobile-menu-icon-open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg id="mobile-menu-icon-close" class="hidden w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span class="sr-only">Menu</span>
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div id="mobile-menu" class="hidden lg:hidden border-t border-gray-100 bg-white">
            <div class="max-w-6xl mx-auto px-4 py-4 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('home') ? 'bg-yellow-50 text-gray-900' : 'text-gray-700 hover:bg-gray-50' }}">{{ __('site.nav.home') }}</a>
                <a href="{{ route('about') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('about') ? 'bg-yellow-50 text-gray-900' : 'text-gray-700 hover:bg-gray-50' }}">{{ __('site.nav.about') }}</a>
                <a href="{{ route('packages.index') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('packages.*') ? 'bg-yellow-50 text-gray-900' : 'text-gray-700 hover:bg-gray-50' }}">{{ __('site.nav.services') }}</a>
                <a href="{{ route('contact.create') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('contact.*') ? 'bg-yellow-50 text-gray-900' : 'text-gray-700 hover:bg-gray-50' }}">{{ __('site.nav.contact') }}</a>
                <div class="flex items-center justify-between gap-3 pt-3 mt-2 border-t border-gray-100">
                    <label for="mobile-locale-selector" class="text-xs font-medium text-gray-500">{{ __('site.nav.language') }}</label>
                    <select id="mobile-locale-selector" data-locale-switch aria-label="{{ __('site.nav.language') }}" class="rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700 focus:border-yellow-600 focus:ring-yellow-600">
                        <option value="{{ route('locale.switch', ['locale' => 'en']) }}" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>English</option>
                        <option value="{{ route('locale.switch', ['locale' => 'sw']) }}" {{ app()->getLocale() === 'sw' ? 'selected' : '' }}>Kiswahili</option>
                    </select>
                </div>
                <div class="pt-2">
                    @auth
                        <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('site.nav.dashboard') }}</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('site.nav.logout') }}</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('site.nav.login') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Flash messages — quiet, dismissible --}}
    @if(session('success'))
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-900 px-4 py-3 rounded-lg flex items-start justify-between gap-3" role="status">
                <span class="text-sm">{{ session('success') }}</span>
                <button type="button" onclick="this.closest('div[role=status]').remove()" class="text-yellow-600 hover:text-yellow-900 leading-none" aria-label="Dismiss">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 w-full">
            <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-start justify-between gap-3" role="alert">
                <span class="text-sm">{{ session('error') }}</span>
                <button type="button" onclick="this.closest('div[role=alert]').remove()" class="text-red-500 hover:text-red-800 leading-none" aria-label="Dismiss">&times;</button>
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    {{-- ============================================
         FOOTER — dark, calm, one accent
    ============================================ --}}
    <footer class="bg-gray-950 text-white">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 lg:gap-10">
                <div class="col-span-2">
                    <img src="{{ asset('images/brand/main-2.svg') }}" alt="Oweru International Ltd logo" class="h-10 w-auto mb-4">
                    <p class="text-gray-400 text-sm leading-relaxed max-w-sm">
                        {{ __('site.footer.tagline') }}
                    </p>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white mb-4">{{ __('site.footer.quick_links') }}</h3>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('about') }}" class="text-gray-400 hover:text-white text-sm transition-colors">{{ __('site.footer.about_us') }}</a></li>
                        <li><a href="{{ route('packages.index') }}" class="text-gray-400 hover:text-white text-sm transition-colors">{{ __('site.footer.our_services') }}</a></li>
                        <li><a href="{{ route('projects.index') }}" class="text-gray-400 hover:text-white text-sm transition-colors">{{ __('site.footer.our_work') }}</a></li>
                        <li><a href="{{ route('faq.index') }}" class="text-gray-400 hover:text-white text-sm transition-colors">{{ __('site.footer.faq') }}</a></li>
                        <li><a href="{{ route('contact.create') }}" class="text-gray-400 hover:text-white text-sm transition-colors">{{ __('site.footer.contact_us') }}</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-sm font-semibold text-white mb-4">{{ __('site.footer.contact') }}</h3>
                    <ul class="space-y-2.5 text-sm text-gray-400">
                        <li><a href="mailto:inf@oweru.com" class="hover:text-white transition-colors">inf@oweru.com</a></li>
                        <li><a href="tel:+255711890764" class="hover:text-white transition-colors">+255 711 890 764</a></li>
                        <li>{{ __('site.footer.location') }}</li>
                    </ul>
                    <div class="flex gap-2 mt-5">
                        <a href="https://www.facebook.com/oweru.co.tz" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/oweru.co.tz" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="https://www.linkedin.com/company/oweru-co-tz" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="w-9 h-9 bg-white/10 rounded-lg flex items-center justify-center hover:bg-white/20 transition-colors">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>
            </div>

            <div class="border-t border-white/10 mt-10 pt-6 flex flex-col sm:flex-row justify-between items-center gap-3">
                <p class="text-gray-500 text-xs">&copy; {{ date('Y') }} Oweru International Ltd. {{ __('site.footer.rights') }}</p>
            </div>
        </div>
    </footer>

    {{-- Quick contact — one calm floating button --}}
    @php
        $whatsappNumber = preg_replace('/[^0-9]/', '', config('owers.company.phone', '+255 711 890 764'));
    @endphp
    @unless(request()->routeIs('contact.*'))
    <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode('Hello Oweru, I would like to ask about your services.') }}"
        target="_blank" rel="noopener noreferrer" aria-label="Chat with us on WhatsApp"
        class="fixed bottom-5 right-5 z-50 w-12 h-12 rounded-full bg-gray-900 text-white shadow-lg flex items-center justify-center hover:bg-black transition-colors print:hidden">
        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.074-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
    </a>
    @endunless

    @stack('scripts')

    {{-- Mobile menu toggle (vanilla JS, no dependencies) --}}
    <script>
        (function () {
            var btn = document.getElementById('mobile-menu-btn');
            var menu = document.getElementById('mobile-menu');
            if (!btn || !menu) return;

            var iconOpen = document.getElementById('mobile-menu-icon-open');
            var iconClose = document.getElementById('mobile-menu-icon-close');

            function setOpen(open) {
                menu.classList.toggle('hidden', !open);
                if (iconOpen && iconClose) {
                    iconOpen.classList.toggle('hidden', open);
                    iconClose.classList.toggle('hidden', !open);
                }
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            btn.addEventListener('click', function () {
                setOpen(menu.classList.contains('hidden'));
            });

            // Close when a link inside the menu is followed
            menu.addEventListener('click', function (e) {
                if (e.target.closest('a')) setOpen(false);
            });

            // Close on Escape and reset state when resizing up to desktop
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setOpen(false);
            });
            window.addEventListener('resize', function () {
                if (window.innerWidth >= 1024) setOpen(false);
            });
        })();
    </script>
</body>
</html>
