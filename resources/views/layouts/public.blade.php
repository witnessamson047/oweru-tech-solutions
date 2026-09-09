<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Oweru International Ltd - Professional Technical Solutions, Website Health Scanning & Digital Services">
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

    <nav class="bg-white shadow-sm border-b border-gray-100 sticky top-0 z-40" style="border-bottom: 2px solid var(--gold) !important;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 bg-yellow-600 rounded-lg flex items-center justify-center">
                            <span class="text-black font-bold text-lg">O</span>
                        </div>
                        <div>
                            <span class="font-bold text-gray-900 text-lg leading-tight block">OWERU</span>
                            <span class="text-[10px] text-gray-500 uppercase tracking-wider leading-tight block">Tech Solutions</span>
                        </div>
                    </a>
                </div>

                <div class="hidden md:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('home') ? 'text-yellow-600' : 'text-gray-600 hover:text-gray-900' }} rounded-lg hover:bg-gray-50 transition">Home</a>
                    <a href="{{ route('packages.index') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('packages.*') ? 'text-yellow-600' : 'text-gray-600 hover:text-gray-900' }} rounded-lg hover:bg-gray-50 transition">Services</a>
                    <a href="{{ route('scanner.index') }}" class="px-3 py-2 text-sm font-medium {{ request()->routeIs('scanner.*') ? 'text-yellow-600' : 'text-gray-600 hover:text-gray-900' }} rounded-lg hover:bg-gray-50 transition">Website Check</a>
                    @auth
                    <form method="POST" action="{{ route('logout') }}" class="inline ml-2">
                        @csrf
                        <button type="submit" class="btn-outline text-sm">Logout</button>
                    </form>
                    <a href="{{ route('admin.dashboard') }}" class="ml-2 px-3 py-2 text-sm font-medium text-yellow-600 hover:text-yellow-700 rounded-lg border border-yellow-300 hover:bg-yellow-50 transition">Dashboard</a>
                    @else
                    <a href="{{ route('enquiry.create') }}" class="ml-2 btn-accent text-sm">Get Started</a>
                    <a href="{{ route('login') }}" class="ml-2 px-3 py-2 text-sm font-medium text-yellow-600 hover:text-yellow-700 rounded-lg border border-yellow-300 hover:bg-yellow-50 transition">Dashboard</a>
                    @endauth
                </div>

                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-btn" class="p-2 rounded-lg text-gray-600 hover:bg-gray-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-100 bg-white">
            <div class="px-4 py-3 space-y-1">
                <a href="{{ route('home') }}" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg">Home</a>
                <a href="{{ route('packages.index') }}" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg">Services</a>
                <a href="{{ route('scanner.index') }}" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg">Website Check</a>
                <a href="{{ route('enquiry.create') }}" class="block px-3 py-2 text-sm font-medium text-black bg-yellow-500 hover:bg-yellow-400 rounded-lg text-center">Get Started</a>
                <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-50 rounded-lg border border-gray-300 text-center">Dashboard</a>
            </div>
        </div>
    </nav>

    @if(session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ session('success') }}
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-black border-l-4 border-gray-600 rounded-lg p-4 flex items-center gap-3">
                <svg class="w-5 h-5 text-black" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-gray-900 text-white mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-10 h-10 bg-yellow-600 rounded-lg flex items-center justify-center">
                            <span class="text-black font-bold text-lg">O</span>
                        </div>
                        <div>
                            <span class="font-bold text-white text-lg leading-tight block">OWERU INTERNATIONAL LTD</span>
                        </div>
                    </div>
                    <p class="text-gray-400 text-sm leading-relaxed max-w-md">
                        Professional technical solutions for individuals, SMEs, and corporations.
                        We diagnose, build, and maintain digital systems that drive business growth.
                    </p>
                    <div class="flex gap-3 mt-4">
                        <a href="https://www.facebook.com/oweru.co.tz" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-yellow-500 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://www.instagram.com/oweru.co.tz" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-pink-600 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                        </a>
                        <a href="https://www.linkedin.com/company/oweru-co-tz" target="_blank" rel="noopener noreferrer" class="w-9 h-9 bg-gray-800 rounded-lg flex items-center justify-center hover:bg-yellow-600 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="font-semibold text-white mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('packages.index') }}" class="text-gray-400 hover:text-white text-sm transition">Our Services</a></li>
                        <li><a href="{{ route('scanner.index') }}" class="text-gray-400 hover:text-white text-sm transition">Free Website Check</a></li>
                        <li><a href="{{ route('enquiry.create') }}" class="text-gray-400 hover:text-white text-sm transition">Contact Us</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-semibold text-white mb-4">Contact</h3>
                    <ul class="space-y-2 text-sm text-gray-400">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            inf@oweru.com
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +255711890764
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Dar es Salaam, Tanzania
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-800 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} Oweru International Ltd. All rights reserved.</p>
                <div class="flex gap-4 text-sm text-gray-500">
                    <a href="/privacy-policy" class="hover:text-white transition">Privacy Policy</a>
                    <a href="/terms-of-service" class="hover:text-white transition">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        document.getElementById('mobile-menu-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu')?.classList.toggle('hidden');
        });
    </script>
    @stack('scripts')
</body>
</html>
