@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Overview of your Oweru Tech Solutions system')

@section('content')

<div class="admin-dashboard space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-green-800">Workspace overview</p>
            <h2 class="mt-1 text-xl font-bold text-gray-900 sm:text-2xl">Welcome back, {{ \Illuminate\Support\Str::before(auth()->user()->name ?? 'Admin', ' ') }}</h2>
            <p class="mt-1 text-xs text-gray-500 sm:text-sm">Your enquiries, websites, and scans at a glance.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2 sm:justify-end">
            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-2 text-xs font-semibold {{ ($scannerHealth['online'] ?? false) ? 'border-green-200 bg-green-50 text-green-700' : 'border-red-200 bg-red-50 text-red-700' }}"
                title="Scanner engine: {{ config('services.scanner.url', 'http://localhost:5000') }} (checked {{ \Illuminate\Support\Carbon::parse($scannerHealth['checked_at'] ?? now())->diffForHumans() }})">
                <span class="h-2 w-2 rounded-full {{ ($scannerHealth['online'] ?? false) ? 'bg-green-500' : 'bg-red-500' }}"></span>
                {{ ($scannerHealth['online'] ?? false) ? 'Scanner online' : 'Scanner offline' }}
            </span>
            <a href="{{ route('admin.websites.create') }}" class="admin-btn-gold px-3 py-2 text-xs">+ Add website</a>
            <a href="{{ route('admin.enquiries.index') }}" class="admin-btn-ghost px-3 py-2 text-xs">View enquiries</a>
        </div>
    </div>

    @if(!($scannerHealth['online'] ?? false) && !empty($scannerHealth['log']['lines']))
        <x-admin.card padded="false" class="overflow-hidden border-red-200">
            <x-slot:title>Scanner engine offline</x-slot:title>
            <x-slot:subtitle>
                Health scans and scraping are paused until the Python engine is back.
                Start it with <span class="font-mono">start.bat</span>, then reload this page.
            </x-slot:subtitle>
            <x-slot:actions>
                <span class="admin-badge admin-badge-danger is-plain font-mono !text-[11px]">{{ $scannerHealth['log']['source'] }}</span>
            </x-slot:actions>
            <pre class="max-h-48 overflow-y-auto whitespace-pre-wrap bg-red-50 px-4 py-3 text-[11px] leading-relaxed text-gray-700">{{ implode("\n", $scannerHealth['log']['lines']) }}</pre>
        </x-admin.card>
    @endif

    {{-- Key Stats — one clean row --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        @foreach([
            ['label' => 'Enquiries', 'value' => $stats['total_enquiries'] ?? 0, 'sub' => 'All time',
             'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'href' => route('admin.enquiries.index')],
            ['label' => 'Websites', 'value' => $stats['websites'] ?? 0, 'sub' => 'Tracked sites',
             'icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9', 'href' => route('admin.websites.index')],
            ['label' => 'Health Scans', 'value' => $stats['total_scans'] ?? 0, 'sub' => 'Total performed',
             'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'href' => route('admin.scans.index')],
            ['label' => 'New this month', 'value' => $stats['new_enquiries'] ?? 0, 'sub' => 'New enquiries',
             'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'href' => route('admin.enquiries.index')],
        ] as $stat)
            <a href="{{ $stat['href'] }}" class="dashboard-stat-card admin-card group block p-3 sm:p-5 {{ $loop->last ? 'is-featured' : '' }}">
                <div class="mb-2 flex items-start justify-between sm:mb-3">
                    <div class="dashboard-stat-icon flex h-9 w-9 items-center justify-center rounded-lg bg-yellow-50 sm:h-10 sm:w-10">
                        <svg class="h-4 w-4 text-yellow-700 sm:h-5 sm:w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stat['icon'] }}"/>
                        </svg>
                    </div>
                    <span class="dashboard-stat-view hidden text-[11px] font-semibold uppercase tracking-wider text-gray-400 sm:inline">View</span>
                </div>
                <div class="dashboard-stat-value text-xl font-bold text-black sm:text-2xl">{{ number_format($stat['value']) }}</div>
                <div class="dashboard-stat-label mt-1 text-[11px] leading-snug text-gray-600 sm:text-xs">{{ $stat['label'] }} · {{ $stat['sub'] }}</div>
            </a>
        @endforeach
    </div>

    {{-- Recent scans + new enquiries --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3 lg:gap-5">
        <section class="admin-card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Recent scans</h3>
                    <p class="mt-0.5 text-[11px] text-gray-500">Latest completed website checks</p>
                </div>
                <a href="{{ route('admin.scans.index') }}" class="whitespace-nowrap text-xs font-semibold text-yellow-700 hover:text-green-900">See all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="dashboard-table">
                    <thead>
                        <tr>
                            <th>Website</th>
                            <th>Score</th>
                            <th>Status</th>
                            <th>Scanned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentScans ?? [] as $scan)
                            <tr class="dashboard-recent-row">
                                <td>
                                    <a href="{{ route('admin.scans.show', $scan) }}" class="dashboard-table-link">
                                        {{ $scan->website->business_name ?? $scan->url }}
                                    </a>
                                    <span class="dashboard-table-subtext">{{ $scan->url }}</span>
                                </td>
                                <td><span class="dashboard-score">{{ $scan->score ?? '-' }}</span></td>
                                <td><span class="dashboard-status">{{ $scan->band ?? 'Completed' }}</span></td>
                                <td class="whitespace-nowrap text-xs text-gray-500">{{ $scan->created_at->diffForHumans(short: true) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-sm text-gray-500">No completed scans yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <aside class="admin-card overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 px-4 py-3 sm:px-5">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">New enquiries</h3>
                    <p class="mt-0.5 text-[11px] text-gray-500">Recent people to follow up</p>
                </div>
                <a href="{{ route('admin.enquiries.index') }}" class="whitespace-nowrap text-xs font-semibold text-yellow-700 hover:text-green-900">See all</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentEnquiries ?? [] as $enquiry)
                    <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="dashboard-enquiry-row">
                        <span class="dashboard-enquiry-avatar">{{ strtoupper(substr($enquiry->name ?: 'C', 0, 1)) }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-xs font-semibold text-gray-900">{{ $enquiry->business_name ?: $enquiry->name }}</span>
                            <span class="mt-0.5 block truncate text-[10px] text-gray-500">{{ $enquiry->package_name ?? $enquiry->email }}</span>
                        </span>
                        <span class="dashboard-enquiry-stage {{ $enquiry->stage === 'new' ? 'is-new' : '' }}">{{ str_replace('_', ' ', ucfirst($enquiry->stage)) }}</span>
                    </a>
                @empty
                    <div class="px-4 py-8 text-center text-sm text-gray-500">No enquiries yet.</div>
                @endforelse
            </div>
        </aside>
    </div>

    {{-- Secondary Stats --}}
    @php
        $secondaryStats = collect([
            ['label' => 'Scored', 'value' => $stats['scored_websites'] ?? 0, 'bar' => ($stats['scored_websites'] ?? 0) * 2],
            ['label' => 'Prospects', 'value' => $stats['prospects'] ?? 0, 'bar' => ($stats['prospects'] ?? 0) * 3],
            ['label' => 'Priority', 'value' => $stats['priority_prospects'] ?? 0, 'bar' => ($stats['priority_prospects'] ?? 0) * 5],
            ['label' => 'Excluded', 'value' => $stats['excluded_websites'] ?? 0, 'bar' => ($stats['excluded_websites'] ?? 0) * 10],
        ])->filter(fn ($stat) => $stat['value'] > 0);
    @endphp
    @if(($showAllStats ?? true) && $secondaryStats->isNotEmpty())
    <div class="grid grid-cols-2 gap-3 sm:gap-4 md:grid-cols-4">
        @foreach($secondaryStats as $stat)
            <div class="admin-card p-3 sm:p-4">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-[10px] font-semibold uppercase tracking-wider text-gray-500 sm:text-xs">{{ $stat['label'] }}</span>
                    <div class="h-1.5 w-1.5 rounded-full bg-yellow-600"></div>
                </div>
                <div class="text-lg font-bold text-gray-900 sm:text-xl">{{ number_format($stat['value']) }}</div>
                <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100">
                    <div class="h-full rounded-full bg-yellow-600 transition-all" style="width: {{ min(100, $stat['bar']) }}%"></div>
                </div>
            </div>
        @endforeach
    </div>
    @endif

    {{-- Score Distribution + Priority Prospects --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="admin-card p-4 sm:p-5 {{ ($lowScoreScans ?? collect())->isEmpty() ? 'lg:col-span-2' : '' }}">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="font-semibold text-black">Score Distribution</h3>
                </div>
                <a href="{{ route('admin.scans.index') }}" class="text-xs text-yellow-700 hover:text-black">View Scans →</a>
            </div>
            <div class="space-y-3">
                @foreach([
                    ['band' => 'Critical', 'color' => 'bg-black', 'step' => 5],
                    ['band' => 'Weak', 'color' => 'bg-yellow-700', 'step' => 5],
                    ['band' => 'Adequate', 'color' => 'bg-yellow-500', 'step' => 3],
                    ['band' => 'Strong', 'color' => 'bg-yellow-300', 'step' => 2],
                ] as $row)
                    <div class="flex items-center gap-3">
                        <div class="w-20 text-xs text-gray-500 font-medium">{{ $row['band'] }}</div>
                        <div class="flex-1 h-3 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $row['color'] }} rounded-full transition-all" style="width: {{ min(100, ($scansByBand[$row['band']] ?? 0) * $row['step']) }}%"></div>
                        </div>
                        <div class="w-8 text-right text-sm font-bold text-black">{{ $scansByBand[$row['band']] ?? 0 }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        @if(($lowScoreScans ?? collect())->isNotEmpty())
        <div class="admin-card p-4 sm:p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                    <h3 class="font-semibold text-black">Priority Prospects</h3>
                </div>
                <a href="{{ route('admin.websites.index') }}" class="text-xs text-yellow-700 hover:text-black">View Sites →</a>
            </div>
            <div class="space-y-2">
                @forelse($lowScoreScans ?? [] as $scan)
                    <a href="{{ route('admin.scans.show', $scan) }}" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-yellow-300 transition">
                        <div class="w-10 h-10 rounded-lg bg-black text-white flex items-center justify-center text-lg font-bold">
                            {{ $scan->score }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-semibold text-black truncate">{{ $scan->website->business_name ?? $scan->url }}</div>
                            <div class="text-xs text-gray-500 truncate">{{ $scan->url }}</div>
                        </div>
                        <svg class="w-4 h-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="text-gray-500 text-sm">No priority prospects</p>
                    </div>
                @endforelse
            </div>
        </div>
        @endif
    </div>

    {{-- Sectors + Pipeline --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="admin-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <h3 class="font-semibold text-black">By Sector</h3>
                </div>
                <a href="{{ route('admin.websites.index') }}" class="text-xs text-yellow-700 hover:text-black">View →</a>
            </div>
            <div class="space-y-1">
                @forelse($websitesBySector ?? [] as $sector => $count)
                    <div class="flex items-center justify-between py-3 px-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-yellow-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </div>
                            <span class="text-sm text-gray-700">{{ $sector ?: 'General' }}</span>
                        </div>
                        <span class="text-sm font-semibold text-black bg-gray-100 rounded-full px-3 py-0.5">{{ $count }}</span>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <p class="text-gray-500 text-sm">No sectors yet</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="admin-card p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <h3 class="font-semibold text-black">Pipeline</h3>
                </div>
                <a href="{{ route('admin.pipeline.index') }}" class="text-xs text-yellow-700 hover:text-black">Full View →</a>
            </div>
            <div class="grid grid-cols-3 gap-2">
                @foreach([
                    ['label' => 'New', 'value' => $pipelineStats['new'] ?? 0],
                    ['label' => 'Qualified', 'value' => $pipelineStats['qualified'] ?? 0],
                    ['label' => 'Diagnostic', 'value' => $pipelineStats['diagnostic_paid'] ?? 0],
                ] as $stage)
                    <div class="text-center p-3 rounded-lg border border-gray-100 bg-white">
                        <div class="text-lg font-bold text-black">{{ $stage['value'] }}</div>
                        <div class="text-[10px] text-gray-500 uppercase font-semibold mt-0.5 tracking-wider">{{ $stage['label'] }}</div>
                    </div>
                @endforeach
            </div>
            <div class="grid grid-cols-2 gap-2 mt-3">
                @foreach([
                    ['label' => 'Proposal', 'value' => $pipelineStats['proposal_sent'] ?? 0],
                    ['label' => 'Won', 'value' => $pipelineStats['won'] ?? 0, 'highlight' => true],
                ] as $stage)
                    <div class="text-center p-2.5 rounded-lg border {{ ($stage['highlight'] ?? false) ? 'border-yellow-400 bg-yellow-50' : 'border-gray-100 bg-white' }}">
                        <div class="text-base font-bold text-black">{{ $stage['value'] }}</div>
                        <div class="text-[10px] {{ ($stage['highlight'] ?? false) ? 'text-yellow-700' : 'text-gray-500' }} uppercase font-semibold tracking-wider">{{ $stage['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Watchdog Activity Feed --}}
    <div class="admin-card overflow-hidden">
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2">
            <div class="flex min-w-0 items-center gap-2">
                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                <h3 class="font-semibold text-black truncate">Watchdog Activity</h3>
                @if(($watchdogHotWeek ?? 0) > 0)
                    <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 border border-red-200 text-[10px] font-semibold uppercase tracking-wider">
                        {{ $watchdogHotWeek }} hot this week
                    </span>
                @endif
            </div>
            <a href="{{ route('admin.scraped-businesses.index') }}" class="text-xs text-yellow-700 hover:text-black">All Businesses →</a>
        </div>
        <div class="divide-y divide-gray-100">
            @forelse(($watchEvents ?? collect())->take(4) as $event)
                @php $biz = $event->business; @endphp
                <a href="{{ $biz ? route('admin.scraped-businesses.show', $biz) : '#' }}" class="block px-5 py-3">
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-semibold uppercase tracking-wider whitespace-nowrap {{ $event->style }}">
                            {{ $event->label }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium text-black truncate">
                                {{ $biz->business_name ?: ($biz->website_url ?? 'Deleted business') }}
                                @if($event->notified_at)
                                    <span class="text-[10px] text-gray-400" title="Staff alerted {{ $event->notified_at->format('d M H:i') }}">🔔</span>
                                @endif
                            </div>
                            @if($event->summary)
                                <div class="text-xs text-gray-500 truncate">{{ $event->summary }}</div>
                            @endif
                        </div>
                        <span class="ml-auto text-[11px] text-gray-400 whitespace-nowrap">{{ $event->created_at->diffForHumans(short: true) }}</span>
                    </div>
                </a>
            @empty
                <div class="px-5 py-8 text-center">
                    <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-gray-500 text-sm">No watchdog events yet — changes appear here as the 24/7 scraper re-visits targets.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Website Discovery + No-Website Outreach --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 admin-card overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between">
                <h3 class="font-semibold text-black">Website Discovery</h3>
                <a href="{{ route('admin.discovery.index') }}" class="text-xs text-yellow-700 hover:text-black">Open Discovery →</a>
            </div>
            <div class="p-5">
                <div class="flex flex-wrap gap-8">
                    <div>
                        <div class="text-2xl font-bold text-black">{{ number_format($stats['discovered_week'] ?? 0) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Websites discovered this week</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-black">{{ number_format(($stats['no_website_leads'] ?? 0) + ($stats['discovery_leads_contacted'] ?? 0)) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Businesses with NO website found</div>
                    </div>
                    <div>
                        <div class="text-2xl font-bold text-black">{{ number_format($stats['discovery_leads_contacted'] ?? 0) }}</div>
                        <div class="text-xs text-gray-500 mt-1">Leads contacted</div>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    @forelse($discoveryRuns ?? [] as $run)
                        <div class="flex items-center justify-between gap-3 text-sm border-t border-gray-50 pt-2">
                            <div class="flex-1 min-w-0 truncate">
                                <span class="font-medium text-black">{{ $run->city }}</span>
                                <span class="text-xs text-gray-500"> · {{ $run->category }} · {{ $run->created_at->diffForHumans() }}</span>
                            </div>
                            @if($run->status === App\Models\DiscoveryRun::STATUS_COMPLETED)
                                <span class="text-xs text-gray-600 whitespace-nowrap">{{ $run->stats['unique_websites'] ?? 0 }} sites · {{ $run->queued_count }} queued · {{ $run->leads_count }} leads</span>
                            @elseif($run->status === App\Models\DiscoveryRun::STATUS_FAILED)
                                <span class="text-xs text-red-600 whitespace-nowrap">failed — {{ \Illuminate\Support\Str::limit($run->error, 60) }}</span>
                            @else
                                <span class="text-xs text-yellow-700 whitespace-nowrap">running…</span>
                            @endif
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm border-t border-gray-50 pt-2">No discovery runs yet — <a href="{{ route('admin.discovery.index') }}" class="text-yellow-700 hover:text-black">find business websites by city</a>.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="bg-black rounded-xl p-5 flex flex-col justify-between">
            <div>
                <h3 class="font-semibold text-white">No-Website Outreach</h3>
                <p class="text-xs text-gray-400 mt-1">Businesses discovery found that have no website at all — the warmest “we'll build you one” conversations.</p>
                <div class="text-3xl font-bold text-yellow-500 mt-3">{{ number_format($stats['no_website_leads'] ?? 0) }}</div>
                <div class="text-xs text-gray-400">waiting to be called</div>
            </div>
            <a href="{{ route('admin.discovery.leads') }}" class="mt-4 admin-btn-gold px-3 py-2 text-xs w-full">📞 Open the outreach list</a>
        </div>
    </div>
</div>

@endsection
