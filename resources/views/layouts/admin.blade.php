<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Admin') &middot; Oweru Tech Solutions</title>
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
    {{-- All sidebar / card / button / table / badge / form styling lives in
         app.css so every admin page shares one kit. --}}
    @stack('styles')
</head>
<body class="admin-portal min-h-screen">

    <a href="#admin-main" class="sr-only focus:not-sr-only focus:absolute focus:z-[60] focus:top-2 focus:left-2 focus:bg-white focus:px-4 focus:py-2 focus:rounded-lg focus:shadow-lg focus:text-sm focus:font-semibold">
        Skip to content
    </a>

    <div class="flex min-h-screen">

        {{-- Mobile backdrop --}}
        <div id="sidebar-backdrop" class="fixed inset-0 z-40 bg-black/50 opacity-0 pointer-events-none transition-opacity lg:hidden" aria-hidden="true"></div>

        {{-- ============================================================
             SIDEBAR — the panel's map. Grouped by what staff are DOING,
             with live counters on the four queues that can pile up.
             ============================================================ --}}
        <aside id="admin-sidebar"
               class="fixed inset-y-0 left-0 z-50 w-[min(16.5rem,85vw)] bg-black transform -translate-x-full lg:translate-x-0 transition-transform flex flex-col"
               aria-label="Admin navigation">
            <div class="h-16 flex items-center px-5 border-b border-white/10 shrink-0">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 min-w-0 group">
                    {{-- Light brand variant: same lockup as the public site's dark
                         footer, so it stays legible on the black sidebar. --}}
                    <img src="{{ asset('images/brand/main-2.svg') }}" alt="Oweru International Ltd logo" class="h-10 w-auto shrink-0">
                    <span class="text-[10px] text-white/60 uppercase tracking-wider leading-tight block">Admin Panel</span>
                </a>
            </div>

            <nav class="p-3 space-y-1 overflow-y-auto flex-1 min-h-0 pb-6">
                @php
                    // Single definition for every nav entry so the active
                    // highlight can never drift from the link itself.
                    // 'count' points at a key from the navCounts view composer.
                    $navGroups = [
                        ['label' => 'Overview', 'items' => [
                            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'dashboard', 'active' => 'admin.dashboard'],
                        ]],
                        ['label' => 'Sales', 'items' => [
                            ['label' => 'Pipeline', 'route' => 'admin.pipeline.index', 'icon' => 'pipeline', 'active' => 'admin.pipeline.*', 'count' => 'enquiries_new', 'alert' => true],
                            ['label' => 'Enquiries', 'route' => 'admin.enquiries.index', 'icon' => 'inbox', 'active' => 'admin.enquiries.*'],
                        ]],
                        ['label' => 'Lead Generation', 'items' => [
                            ['label' => 'Website Discovery', 'route' => 'admin.discovery.index', 'icon' => 'map', 'active' => 'admin.discovery.index'],
                            ['label' => 'No-Website Leads', 'route' => 'admin.discovery.leads', 'icon' => 'users', 'active' => 'admin.discovery.leads*', 'count' => 'leads_new'],
                            ['label' => 'Auto-Scraper Queue', 'route' => 'admin.scrape-targets.index', 'icon' => 'clock', 'active' => 'admin.scrape-targets.*', 'count' => 'targets_active'],
                            ['label' => 'Scraper', 'route' => 'admin.scraped-businesses.index', 'icon' => 'folder', 'active' => 'admin.scraped-businesses.*'],
                        ]],
                        ['label' => 'Analysis', 'items' => [
                            ['label' => 'Websites', 'route' => 'admin.websites.index', 'icon' => 'globe', 'active' => 'admin.websites.*'],
                            ['label' => 'Scans', 'route' => 'admin.scans.index', 'icon' => 'pulse', 'active' => 'admin.scans.*'],
                            ['label' => 'Reports', 'route' => 'admin.reports.index', 'icon' => 'document-text', 'active' => 'admin.reports.*'],
                        ]],
                        ['label' => 'Catalog', 'items' => [
                            ['label' => 'Service Packages', 'route' => 'admin.packages.index', 'icon' => 'package', 'active' => 'admin.packages.*'],
                            ['label' => 'Care Plans', 'route' => 'admin.care-plans.index', 'icon' => 'care-plan', 'active' => 'admin.care-plans.*'],
                            ['label' => 'Package Exclusions', 'route' => 'admin.package-exclusions.index', 'icon' => 'shield-off', 'active' => 'admin.package-exclusions.*'],
                        ]],
                        ['label' => 'Scanner Tuning', 'items' => [
                            ['label' => 'Recommendations', 'route' => 'admin.recommendations.index', 'icon' => 'bulb', 'active' => 'admin.recommendations.*'],
                            ['label' => 'Scanner Checks', 'route' => 'admin.scanner-checks.index', 'icon' => 'checklist', 'active' => 'admin.scanner-checks.*'],
                        ]],
                        ['label' => 'Money', 'items' => [
                            ['label' => 'Invoices', 'route' => 'admin.invoices.index', 'icon' => 'receipt', 'active' => 'admin.invoices.*', 'count' => 'invoices_overdue', 'alert' => true],
                            ['label' => 'Payments', 'route' => 'admin.payments.index', 'icon' => 'credit-card', 'active' => 'admin.payments.*'],
                        ]],
                    ];
                @endphp

                @foreach ($navGroups as $group)
                    <div class="sidebar-section-label px-3 pt-4 pb-1.5 font-semibold uppercase">{{ $group['label'] }}</div>
                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            <a href="{{ route($item['route']) }}"
                               class="sidebar-link {{ request()->routeIs($item['active']) ? 'active' : '' }}"
                               @if(request()->routeIs($item['active'])) aria-current="page" @endif>
                                <x-admin.icon :name="$item['icon']" />
                                <span class="truncate">{{ $item['label'] }}</span>
                                @if(!empty($item['count']) && ($navCounts[$item['count']] ?? 0) > 0)
                                    <span class="sidebar-count {{ !empty($item['alert']) ? 'is-alert' : '' }}"
                                          title="{{ number_format($navCounts[$item['count']]) }} waiting for attention">
                                        {{ number_format($navCounts[$item['count']]) }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @endforeach
            </nav>

            {{-- Staff footer pinned to the sidebar base: identity + a logout
                 that is always visible, not tucked behind the avatar menu. --}}
            @auth
                <div class="shrink-0 border-t border-white/10 p-3">
                    <div class="flex items-center gap-2.5 px-2 pb-2 min-w-0">
                        <span class="w-8 h-8 shrink-0 bg-white text-green-900 rounded-full flex items-center justify-center text-xs font-bold">
                            {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                        </span>
                        <span class="min-w-0">
                            <span class="block text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Staff' }}</span>
                            <span class="block text-[10px] text-white/50 capitalize truncate">{{ auth()->user()->role ?? '' }}</span>
                        </span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2 text-sm font-medium text-white/80 hover:bg-white/10 hover:text-white transition">
                            <x-admin.icon name="logout" class="w-4 h-4" />
                            <span>{{ __('site.nav.logout') }}</span>
                        </button>
                    </form>
                </div>
            @endauth
        </aside>

        <div class="flex-1 min-w-0 lg:ml-64">
            {{-- ============ TOPBAR ============ --}}
            <header class="admin-topbar h-16 flex items-center justify-between gap-3 px-4 lg:px-6 sticky top-0 z-40">
                <div class="flex items-center gap-3 min-w-0">
                    <button id="sidebar-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false"
                            class="lg:hidden p-2 rounded-lg text-gray-600 hover:bg-gray-100 transition">
                        <x-admin.icon name="dashboard" class="hidden" />
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="min-w-0">
                        <h1 class="text-base lg:text-lg font-semibold text-gray-900 truncate leading-tight">@yield('page-title', 'Dashboard')</h1>
                        @hasSection('page-subtitle')
                            <p class="text-xs text-gray-500 truncate max-w-[42vw] sm:max-w-none">@yield('page-subtitle')</p>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-1.5 sm:gap-3">
                    {{-- Global quick search: jump straight to the two things
                         staff look for most. --}}
                    <a href="{{ route('admin.scraped-businesses.index') }}" title="Find a business" aria-label="Find a business"
                       class="hidden sm:inline-flex p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
                        <x-admin.icon name="search" class="w-4 h-4" />
                    </a>
                    <a href="{{ route('admin.account.password') }}" title="{{ __('auth.password_change.nav') }}" aria-label="{{ __('auth.password_change.nav') }}"
                       class="inline-flex p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
                        <x-admin.icon name="key" class="w-4 h-4" />
                        <span class="hidden lg:inline text-sm ml-1">{{ __('auth.password_change.nav') }}</span>
                    </a>
                    <a href="{{ route('home') }}" target="_blank" rel="noopener" title="View public website" aria-label="View public website"
                       class="inline-flex p-2 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
                        <x-admin.icon name="external" class="w-4 h-4" />
                        <span class="hidden lg:inline text-sm ml-1">View Site</span>
                    </a>

                    {{-- User menu: identity + logout in one place, rather than a
                         bare "Logout" word floating in the sidebar header. --}}
                    @auth
                        <div class="relative" data-user-menu>
                            <button type="button" data-user-menu-toggle
                                    class="flex items-center gap-2 pl-1.5 pr-2 py-1 rounded-full hover:bg-gray-100 transition"
                                    aria-expanded="false" aria-haspopup="true">
                                <span class="w-8 h-8 shrink-0 bg-green-900 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </span>
                                <span class="hidden md:block text-left leading-tight">
                                    <span class="block text-xs font-semibold text-gray-800 admin-truncate max-w-[8rem]">{{ auth()->user()->name ?? 'Staff' }}</span>
                                    <span class="block text-[10px] text-gray-500 capitalize">{{ auth()->user()->role ?? '' }}</span>
                                </span>
                            </button>
                            <div data-user-menu-panel
                                 class="hidden absolute right-0 mt-2 w-56 admin-card p-1.5 shadow-lg z-50">
                                <div class="px-2.5 py-2 border-b border-gray-100 mb-1">
                                    <p class="text-sm font-semibold text-gray-900 admin-truncate">{{ auth()->user()->name ?? 'Staff' }}</p>
                                    <p class="text-xs text-gray-500 admin-truncate">{{ auth()->user()->email ?? '' }}</p>
                                </div>
                                <a href="{{ route('admin.account.password') }}" class="admin-action w-full !justify-start">
                                    <x-admin.icon name="key" /> Change password
                                </a>
                                <a href="{{ route('home') }}" target="_blank" rel="noopener" class="admin-action w-full !justify-start">
                                    <x-admin.icon name="external" /> View public site
                                </a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="admin-action is-danger w-full !justify-start">
                                        <x-admin.icon name="logout" /> Log out
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            <main id="admin-main" class="p-4 lg:p-6 max-w-[100rem]">
                {{-- Flash: one component, three tones. Dismissable so a long
                     list page is not permanently shortened by a message. --}}
                @foreach ([
                    ['success', 'check-circle', session('success')],
                    ['error', 'x-circle', session('error')],
                    ['info', 'info', session('status')],
                ] as [$tone, $icon, $message])
                    @if ($message)
                        <div class="admin-flash is-{{ $tone }} is-dismissible" role="status">
                            <x-admin.icon :name="$icon" />
                            <p class="min-w-0 flex-1">{{ $message }}</p>
                            <button type="button" class="admin-flash-close" aria-label="Dismiss">&times;</button>
                        </div>
                    @endif
                @endforeach

                @if ($errors->any())
                    <div class="admin-flash is-error" role="alert">
                        <x-admin.icon name="warning" />
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold">
                                {{ trans_choice('There is :count problem with this form.|There are :count problems with this form.', $errors->count(), ['count' => $errors->count()]) }}
                            </p>
                            <ul class="mt-1 list-disc list-inside space-y-0.5 text-[13px]">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="px-4 lg:px-6 py-4 text-center text-xs text-gray-400">
                Oweru Tech Solutions &middot; Admin &middot; all times shown in your local timezone
            </footer>
        </div>
    </div>

    {{-- Flash auto-dismiss + smooth in-page anchors --}}
    <script>
        (function () {
            const sidebar = document.getElementById('admin-sidebar');
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebarBackdrop = document.getElementById('sidebar-backdrop');

            const setSidebarOpen = (open) => {
                if (!sidebar) return;
                sidebar.classList.toggle('-translate-x-full', !open);
                sidebarBackdrop.classList.toggle('opacity-0', !open);
                sidebarBackdrop.classList.toggle('pointer-events-none', !open);
                sidebarToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            };

            if (sidebar) {
                sidebarToggle.addEventListener('click', () => {
                    setSidebarOpen(sidebar.classList.contains('-translate-x-full'));
                });
                sidebarBackdrop.addEventListener('click', () => setSidebarOpen(false));
                sidebar.addEventListener('click', (event) => {
                    if (event.target.closest('a')) setSidebarOpen(false);
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') setSidebarOpen(false);
                });
            }

            // Dismissible flash messages.
            document.querySelectorAll('.admin-flash.is-dismissible').forEach((flash) => {
                const close = flash.querySelector('.admin-flash-close');
                const dismiss = () => {
                    flash.style.transition = 'opacity 200ms ease, transform 200ms ease';
                    flash.style.opacity = '0';
                    flash.style.transform = 'translateY(-4px)';
                    setTimeout(() => flash.remove(), 220);
                };
                if (close) close.addEventListener('click', dismiss);
                setTimeout(dismiss, 9000);
            });

            // Any GET filter form submits on change (select / checkbox), so
            // staff do not have to hunt for the Apply button.
            document.querySelectorAll('form[data-auto-submit]').forEach((form) => {
                form.querySelectorAll('select, input[type="checkbox"], input[type="radio"]').forEach((control) => {
                    control.addEventListener('change', () => form.submit());
                });
            });

            // Staff user menu (no framework — just show/hide).
            const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
            const userMenuPanel = document.querySelector('[data-user-menu-panel]');
            if (userMenuToggle && userMenuPanel) {
                const closeUserMenu = () => {
                    userMenuPanel.classList.add('hidden');
                    userMenuToggle.setAttribute('aria-expanded', 'false');
                };
                userMenuToggle.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const willOpen = userMenuPanel.classList.contains('hidden');
                    userMenuPanel.classList.toggle('hidden', !willOpen);
                    userMenuToggle.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
                });
                document.addEventListener('click', (event) => {
                    if (!event.target.closest('[data-user-menu]')) closeUserMenu();
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape') closeUserMenu();
                });
            }
        })();
    </script>
    @stack('scripts')
</body>
</html>