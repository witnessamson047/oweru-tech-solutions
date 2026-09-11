@extends('layouts.admin')

@section('title', 'Scan Results')
@section('page-title', 'Scan Results')
@section('page-subtitle', $scan->website->business_name ?? $scan->url)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Score Overview --}}
        <div class="card">
            <div class="flex flex-col sm:flex-row items-center gap-6">
                @php $score = $scan->score ?? 0; @endphp
                <div class="w-28 h-28 rounded-full border-6 flex items-center justify-center font-extrabold text-3xl
                    {{ $score < 40 ? 'border-black text-white bg-gray-100' : ($score < 60 ? 'border-yellow-600 text-yellow-800 bg-yellow-50' : ($score < 80 ? 'border-yellow-500 text-yellow-800 bg-yellow-50' : 'border-yellow-400 text-yellow-800 bg-yellow-50')) }}">
                    {{ $score }}
                </div>
                <div class="text-center sm:text-left">
                    <h2 class="text-2xl font-bold text-gray-900">{{ $scan->website->business_name ?? 'Unknown' }}</h2>
                    <p class="text-sm text-gray-500">{{ $scan->url }}</p>
                    <div class="flex items-center gap-3 mt-2">
                        <span class="badge {{ $score >= 80 ? 'badge-success' : ($score >= 60 ? 'badge-info' : ($score >= 40 ? 'badge-warning' : 'badge-danger')) }} text-sm">
                            {{ $scan->band }} ({{ $score }}/100)
                        </span>
                        <span class="text-xs text-gray-400">{{ $scan->started_at?->format('d M Y H:i') ?? $scan->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>
                <div class="sm:ml-auto flex gap-2">
                    <a href="{{ route('admin.reports.generate', $scan) }}" class="btn-primary text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Generate Report
                    </a>
                    <button onclick="scanWebsite({{ $scan->website_id }})" class="btn-outline text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m0 0H1m0 0a8.000 8.000 0 00-1.029 1.68M15.58 16H9m3.366 3.366A8.002 8.002 0 0021.42 20H15.58m0 0a8.001 8.001 0 01-9.033-2M1 13.58V19m0 0a8.003 8.003 0 005.075 2.29l.075.072M10.5 18.5H9"/></svg>
                        Re-scan
                    </button>
                </div>
            </div>
        </div>

        {{-- Individual Check Results --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Check Results ({{ $scan->results->count() }} checks)</h3>

            {{-- Grouped by area --}}
            @php
                $grouped = $scan->results->groupBy('area');
            @endphp

            <div class="space-y-6">
                @foreach($grouped as $area => $results)
                    @php
                        $areaPassed = $results->where('passed', true)->count();
                        $areaTotal = $results->count();
                        $areaPoints = $results->where('passed', true)->sum('points');
                        $areaMaxPoints = $results->sum('points');
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                @switch($area)
                                    @case('Security')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg> @break
                                    @case('Mobile')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg> @break
                                    @case('Speed')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg> @break
                                    @case('Function')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg> @break
                                    @case('Findability')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg> @break
                                    @case('Trust')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg> @break
                                    @case('Commerce')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg> @break
                                    @case('Freshness')<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg> @break
                                    @default<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                @endswitch
                                {{ $area }}
                            </h4>
                            <span class="text-sm font-medium {{ $areaPassed === $areaTotal ? 'text-yellow-600' : ($areaPassed > $areaTotal / 2 ? 'text-yellow-600' : 'text-black') }}">
                                {{ $areaPassed }}/{{ $areaTotal }} passed • {{ $areaPoints }}/{{ $areaMaxPoints }} pts
                            </span>
                        </div>
                        <div class="progress-bar mb-3">
                            <div class="progress-bar-fill {{ $areaPassed === $areaTotal ? 'bg-yellow-500' : ($areaPassed > $areaTotal / 2 ? 'bg-yellow-500' : 'bg-black') }}"
                                style="width: {{ $areaMaxPoints > 0 ? ($areaPoints / $areaMaxPoints * 100) : 0 }}%"></div>
                        </div>
                        <div class="space-y-2">
                            @foreach($results as $result)
                                <div class="flex items-start gap-3 p-3 rounded-lg {{ $result->passed ? 'bg-yellow-50' : 'bg-gray-100' }}">
                                    <span class="mt-0.5">
                                        @if($result->passed)
                                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        @endif
                                    </span>
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-sm text-gray-900">{{ $result->check->name ?? $result->check_name }}</p>
                                            <span class="text-xs {{ $result->passed ? 'text-yellow-600' : 'text-black' }}">
                                                {{ $result->passed ? '+' . $result->points . ' pts' : '0 pts' }}
                                            </span>
                                        </div>
                                        @if($result->finding_text)
                                            <p class="text-sm text-gray-600 mt-1">{{ $result->finding_text }}</p>
                                        @endif
                                        @if($result->evidence)
                                            <p class="text-xs text-gray-400 mt-1 font-mono">{{ Str::limit($result->evidence, 150) }}</p>
                                        @endif
                                        @if(!$result->passed && $result->recommendation)
                                            <div class="mt-2 p-2 rounded bg-yellow-50 border-l-2 border-yellow-500">
                                                <p class="text-xs text-gray-700">
                                                    <span class="inline-flex items-center gap-1 font-semibold text-yellow-800"><svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v4a1 1 0 102 0V7zm-1 8a1 1 0 100-2 1 1 0 000 2z"/></svg> Offer:</span>
                                                    {{ $result->recommendation->solution }}
                                                    <span class="badge badge-info text-[10px] ml-1">{{ $result->recommendation->service_type }}</span>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Score Band Legend --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Score Bands</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-yellow-400"></span>
                    <span class="text-gray-700">Strong: 80–100</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                    <span class="text-gray-700">Adequate: 60–79</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-yellow-600"></span>
                    <span class="text-gray-700">Weak: 40–59</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-black"></span>
                    <span class="text-gray-700">Critical: Under 40</span>
                </div>
            </div>
        </div>

        {{-- Top Findings --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Top 5 Findings</h3>
            <div class="space-y-2">
                @foreach($scan->results->where('passed', false)->sortBy('points')->take(5) as $finding)
                    <div class="p-2 bg-gray-100 rounded-lg text-sm">
                        <p class="font-medium text-black text-xs">{{ $finding->check->name ?? $finding->check_name }}</p>
                        <p class="text-gray-600 text-xs mt-0.5">{{ Str::limit($finding->finding_text, 80) }}</p>
                    </div>
                @endforeach
                @if($scan->results->where('passed', false)->count() === 0)
                    <p class="text-sm text-gray-400 inline-flex items-center gap-1">All checks passed
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </p>
                @endif
            </div>
        </div>

        {{-- Scan Meta --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Scan Details</h3>
            <div class="space-y-2 text-xs text-gray-500">
                <div class="flex justify-between">
                    <span>Status</span>
                    <span class="badge {{ $scan->status === 'completed' ? 'badge-success' : 'badge-warning' }}">{{ ucfirst($scan->status) }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Started</span>
                    <span>{{ $scan->started_at?->format('d M Y H:i') ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Completed</span>
                    <span>{{ $scan->completed_at?->format('d M Y H:i') ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Source</span>
                    <span>{{ ucfirst($scan->source ?? 'manual') }}</span>
                </div>
            </div>
        </div>

        {{-- Related Enquiry --}}
        @if($scan->enquiry)
            <div class="card">
                <h3 class="font-bold text-gray-900 mb-3">Related Enquiry</h3>
                <a href="{{ route('admin.enquiries.show', $scan->enquiry) }}" class="text-sm text-yellow-600 hover:underline">
                    {{ $scan->enquiry->name }} — {{ $scan->enquiry->business_name }}
                </a>
            </div>
        @endif
    </div>
</div>

@endsection
