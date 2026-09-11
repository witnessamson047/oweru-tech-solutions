@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your Oweru Tech Solutions system')

@section('content')

<div class="space-y-6">
    {{-- Welcome Banner with Illustration --}}
    <div class="bg-black rounded-xl p-6 mb-6">
        <div class="flex items-center gap-6">
            <div class="flex-1">
                <h2 class="text-xl font-bold text-white mb-1">Welcome to Oweru Admin</h2>
                <p class="text-sm text-gray-400">Manage your clients, scans, and pipeline all in one place.</p>
                <div class="mt-3 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium {{ ($scannerHealth['online'] ?? false) ? 'bg-green-500/10 text-green-400 border border-green-500/30' : 'bg-red-500/10 text-red-400 border border-red-500/30' }}"
                    title="Scanner engine: {{ config('services.scanner.url', 'http://localhost:5000') }} (checked {{ \Illuminate\Support\Carbon::parse($scannerHealth['checked_at'] ?? now())->diffForHumans() }})">
                    <span class="relative flex h-2 w-2">
                        @if($scannerHealth['online'] ?? false)
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        @endif
                        <span class="relative inline-flex rounded-full h-2 w-2 {{ ($scannerHealth['online'] ?? false) ? 'bg-green-400' : 'bg-red-500' }}"></span>
                    </span>
                    @if($scannerHealth['online'] ?? false)
                        Scanner engine online
                    @else
                        Scanner engine offline — start it with C:\python312\python.exe scanner\scanner.py
                    @endif
                </div>

                @if(!($scannerHealth['online'] ?? false) && !empty($scannerHealth['log_tail']))
                    <div class="mt-3 max-w-2xl rounded-lg border border-red-500/30 bg-black/60 overflow-hidden">
                        <div class="flex items-center justify-between px-3 py-1.5 border-b border-red-500/20">
                            <span class="text-[11px] uppercase tracking-wider text-red-400/80">scanner-service.log — last {{ count($scannerHealth['log_tail']) }} lines</span>
                            <span class="text-[11px] text-gray-500">newest last</span>
                        </div>
                        <pre class="px-3 py-2 text-[11px] leading-relaxed text-gray-300 max-h-48 overflow-y-auto whitespace-pre-wrap font-mono">{{ implode("\n", $scannerHealth['log_tail']) }}</pre>
                    </div>
                @endif
            </div>
            <div class="w-20 h-20 bg-yellow-500/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>
    </div>

    {{-- Stats Cards with Mini Illustrations --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Total</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['total_enquiries'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Enquiries</div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">New</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['new_enquiries'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">This month</div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Scans</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['total_scans'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Total performed</div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 0118 0 9 9 0 01-9-9m-9 9a9 9 0 01-9 9"/>
                    </svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Sites</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['websites'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Websites</div>
        </div>
    </div>

    {{-- Secondary Stats with Visual Bars --}}
    @if(($showAllStats ?? true) && ($stats['scored_websites'] > 0 || $stats['prospects'] > 0 || $stats['priority_prospects'] > 0))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100/50 rounded-lg p-4 border border-yellow-200">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="text-xs text-yellow-700 font-medium uppercase">Scored</span>
            </div>
            <div class="text-xl font-bold text-black">{{ number_format($stats['scored_websites'] ?? 0) }}</div>
            <div class="mt-2 h-1.5 bg-yellow-200 rounded-full overflow-hidden">
                <div class="h-full bg-yellow-500 rounded-full" style="width: {{ ($stats['scored_websites'] ?? 0) * 2 }}%"></div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-yellow-100/50 rounded-lg p-4 border border-yellow-200">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="text-xs text-yellow-700 font-medium uppercase">Prospects</span>
            </div>
            <div class="text-xl font-bold text-black">{{ number_format($stats['prospects'] ?? 0) }}</div>
            <div class="mt-2 h-1.5 bg-yellow-200 rounded-full overflow-hidden">
                <div class="h-full bg-yellow-500 rounded-full" style="width: {{ ($stats['prospects'] ?? 0) * 3 }}%"></div>
            </div>
        </div>

        <div class="bg-black rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                <span class="text-xs text-yellow-400 font-medium uppercase">Priority</span>
            </div>
            <div class="text-xl font-bold text-white">{{ number_format($stats['priority_prospects'] ?? 0) }}</div>
            <div class="mt-2 h-1.5 bg-gray-600 rounded-full overflow-hidden">
                <div class="h-full bg-yellow-500 rounded-full" style="width: {{ ($stats['priority_prospects'] ?? 0) * 5 }}%"></div>
            </div>
        </div>

        <div class="bg-black rounded-lg p-4">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                <span class="text-xs text-yellow-400 font-medium uppercase">Excluded</span>
            </div>
            <div class="text-xl font-bold text-white">{{ number_format($stats['excluded_websites'] ?? 0) }}</div>
            <div class="mt-2 h-1.5 bg-gray-600 rounded-full overflow-hidden">
                <div class="h-full bg-gray-500 rounded-full" style="width: {{ ($stats['excluded_websites'] ?? 0) * 10 }}%"></div>
            </div>
        </div>
    </div>
    @endif

    {{-- Primary Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Total</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['total_enquiries'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Enquiries</div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">New</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['new_enquiries'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Enquiries</div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Scans</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['total_scans'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Total</div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 bg-yellow-50 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03 3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                </div>
                <span class="text-xs font-semibold text-yellow-600 uppercase tracking-wider">Sites</span>
            </div>
            <div class="text-2xl font-bold text-black">{{ number_format($stats['websites'] ?? 0) }}</div>
            <div class="text-xs text-gray-400 mt-1">Websites</div>
        </div>
    </div>

    {{-- Compact Secondary Stats Row --}}
    @if(($showAllStats ?? true) && ($stats['scored_websites'] > 0 || $stats['prospects'] > 0))
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-2">
        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-100">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <span class="text-xs text-yellow-700 font-medium uppercase">Scored</span>
            </div>
            <div class="text-lg font-bold text-black">{{ number_format($stats['scored_websites'] ?? 0) }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-100">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2"/></svg>
                <span class="text-xs text-yellow-700 font-medium uppercase">Prospects</span>
            </div>
            <div class="text-lg font-bold text-black">{{ number_format($stats['prospects'] ?? 0) }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-100">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                <span class="text-xs text-yellow-700 font-medium uppercase">Priority</span>
            </div>
            <div class="text-lg font-bold text-black">{{ number_format($stats['priority_prospects'] ?? 0) }}</div>
        </div>
        <div class="bg-yellow-50 rounded-lg p-3 border border-yellow-100">
            <div class="flex items-center gap-2 mb-1">
                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <span class="text-xs text-yellow-700 font-medium uppercase">Excluded</span>
            </div>
            <div class="text-lg font-bold text-black">{{ number_format($stats['excluded_websites'] ?? 0) }}</div>
        </div>
    </div>
    @endif

    {{-- Two Column Layout --}}

    {{-- Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Score Distribution with Visual Bars --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="font-semibold text-black">Score Distribution</h3>
                </div>
                <a href="{{ route('admin.scans.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700">View Scans</a>
            </div>
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <div class="w-20 text-xs text-gray-500 font-medium">Critical</div>
                    <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-black rounded-full transition-all" style="width: {{ ($scansByBand['Critical'] ?? 0) * 5 }}%"></div>
                    </div>
                    <div class="w-8 text-right text-sm font-bold text-black">{{ $scansByBand['Critical'] ?? 0 }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-20 text-xs text-gray-500 font-medium">Weak</div>
                    <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-yellow-700 rounded-full transition-all" style="width: {{ ($scansByBand['Weak'] ?? 0) * 5 }}%"></div>
                    </div>
                    <div class="w-8 text-right text-sm font-bold text-black">{{ $scansByBand['Weak'] ?? 0 }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-20 text-xs text-gray-500 font-medium">Adequate</div>
                    <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-yellow-500 rounded-full transition-all" style="width: {{ ($scansByBand['Adequate'] ?? 0) * 3 }}%"></div>
                    </div>
                    <div class="w-8 text-right text-sm font-bold text-black">{{ $scansByBand['Adequate'] ?? 0 }}</div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-20 text-xs text-gray-500 font-medium">Strong</div>
                    <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-yellow-300 rounded-full transition-all" style="width: {{ ($scansByBand['Strong'] ?? 0) * 2 }}%"></div>
                    </div>
                    <div class="w-8 text-right text-sm font-bold text-black">{{ $scansByBand['Strong'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        {{-- Priority Prospects with Icons --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <h3 class="font-semibold text-black">Priority Prospects</h3>
                </div>
                <a href="{{ route('admin.websites.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700">View Sites</a>
            </div>
            <div class="space-y-2">
                @forelse($lowScoreScans ?? [] as $scan)
                    <a href="{{ route('admin.scans.show', $scan) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-yellow-200 hover:bg-yellow-50/40 transition group">
                        <div class="w-10 h-10 rounded-lg bg-black text-white flex items-center justify-center text-lg font-bold group-hover:bg-yellow-600 group-hover:text-white transition">
                            {{ $scan->score }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-black truncate">{{ $scan->website->business_name ?? $scan->url }}</div>
                            <div class="text-xs text-gray-400 truncate">{{ $scan->url }}</div>
                        </div>
                        <div class="w-2 h-2 rounded-full bg-black flex-shrink-0"></div>
                    </a>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-gray-400 text-sm">No priority prospects</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Second Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Sectors with Icons --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <h3 class="font-semibold text-black">By Sector</h3>
                </div>
                <a href="{{ route('admin.websites.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700">View</a>
            </div>
            <div class="space-y-1">
                @forelse($websitesBySector ?? [] as $sector => $count)
                    <div class="flex items-center justify-between py-3 px-3 rounded-lg hover:bg-gray-50 transition group">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-600">{{ $sector ?: 'General' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-yellow-500 rounded-full" style="width: 100%"></div>
                            </div>
                            <span class="text-sm font-semibold text-black w-6 text-right">{{ $count }}</span>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M29 21V5a2 2 0 00-2-2H5a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="text-gray-400 text-sm">No sectors yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Pipeline Visual --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="font-semibold text-black">Pipeline</h3>
                </div>
                <a href="{{ route('admin.pipeline.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700">Full View</a>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-lg font-bold text-black">{{ $pipelineStats['new'] ?? 0 }}</div>
                    <div class="text-[10px] text-gray-500 uppercase font-medium mt-0.5">New</div>
                    <div class="w-16 h-1 bg-gray-200 rounded-full mx-auto mt-1 overflow-hidden">
                        <div class="h-full bg-black rounded-full" style="width: {{ ($pipelineStats['new'] ?? 0) * 8 }}%"></div>
                    </div>
                </div>
                <div class="text-center p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="text-lg font-bold text-black">{{ $pipelineStats['qualified'] ?? 0 }}</div>
                    <div class="text-[10px] text-yellow-700 uppercase font-medium mt-0.5">Qualified</div>
                    <div class="w-16 h-1 bg-yellow-200 rounded-full mx-auto mt-1 overflow-hidden">
                        <div class="h-full bg-yellow-600 rounded-full" style="width: {{ ($pipelineStats['qualified'] ?? 0) * 8 }}%"></div>
                    </div>
                </div>
                <div class="text-center p-3 bg-gray-50 rounded-lg border border-gray-200">
                    <div class="text-lg font-bold text-black">{{ $pipelineStats['diagnostic_paid'] ?? 0 }}</div>
                    <div class="text-[10px] text-gray-500 uppercase font-medium mt-0.5">Diagnostic</div>
                    <div class="w-16 h-1 bg-gray-200 rounded-full mx-auto mt-1 overflow-hidden">
                        <div class="h-full bg-gray-600 rounded-full" style="width: {{ ($pipelineStats['diagnostic_paid'] ?? 0) * 8 }}%"></div>
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2 mt-3">
                <div class="text-center p-2 bg-yellow-50 rounded-lg border border-yellow-200">
                    <div class="text-base font-bold text-black">{{ $pipelineStats['proposal_sent'] ?? 0 }}</div>
                    <div class="text-[9px] text-yellow-700 uppercase font-medium">Proposal</div>
                </div>
                <div class="text-center p-2 bg-yellow-100 rounded-lg border border-yellow-300">
                    <div class="text-base font-bold text-black">{{ $pipelineStats['won'] ?? 0 }}</div>
                    <div class="text-[9px] text-yellow-800 uppercase font-medium">Won</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tables Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Enquiries --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-black">Recent Enquiries</h3>
                <a href="{{ route('admin.enquiries.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700">See All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <tbody class="divide-y divide-gray-100">
                        @forelse($recentEnquiries ?? [] as $enquiry)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-5 py-3">
                                    <div class="font-medium text-black text-sm">{{ $enquiry->business_name }}</div>
                                    <div class="text-xs text-gray-400">{{ $enquiry->name }}</div>
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-600">{{ $enquiry->package_name ?? '-' }}</td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $enquiry->stage === 'new' ? 'bg-yellow-100 text-yellow-800' : ($enquiry->stage === 'qualified' ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                        {{ str_replace('_', ' ', ucfirst($enquiry->stage)) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-400 whitespace-nowrap">{{ $enquiry->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">No enquiries yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Recent Scans --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-black">Recent Scans</h3>
                <a href="{{ route('admin.scans.index') }}" class="text-xs text-yellow-600 hover:text-yellow-700">See All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentScans ?? [] as $scan)
                    <a href="{{ route('admin.scans.show', $scan) }}" class="block px-5 py-3 hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center text-sm font-bold {{ $scan->score < 40 ? 'bg-black text-white' : ($scan->score < 60 ? 'bg-yellow-700 text-white' : ($scan->score < 80 ? 'bg-yellow-500 text-black' : 'bg-yellow-300 text-black')) }}">
                                {{ $scan->score ?? '-' }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-medium text-black truncate">{{ $scan->website->business_name ?? $scan->url }}</div>
                                <div class="text-xs text-gray-400 truncate">{{ $scan->url }}</div>
                            </div>
                            <span class="text-xs px-2 py-0.5 rounded {{ ($scan->score ?? 0) >= 80 ? 'bg-yellow-100 text-yellow-800' : (($scan->score ?? 0) >= 60 ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                {{ $scan->band ?? '-' }}
                            </span>
                        </div>
                    </a>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-8 text-center text-gray-400 text-sm">No scans yet</td>
                    </tr>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
