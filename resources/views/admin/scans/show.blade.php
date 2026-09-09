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
                    <a href="{{ route('admin.reports.generate', $scan) }}" class="btn-primary text-sm">📄 Generate Report</a>
                    <button onclick="scanWebsite({{ $scan->website_id }})" class="btn-outline text-sm">🔄 Re-scan</button>
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
                                    @case('Security') 🔒 @break
                                    @case('Mobile') 📱 @break
                                    @case('Speed') ⚡ @break
                                    @case('Function') ⚙️ @break
                                    @case('Findability') 🔍 @break
                                    @case('Trust') 🛡️ @break
                                    @case('Commerce') 🛒 @break
                                    @case('Freshness') 🕐 @break
                                    @default 📋
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
                                    <span class="mt-0.5 {{ $result->passed ? 'text-yellow-600' : 'text-black' }}">
                                        {{ $result->passed ? '✓' : '✕' }}
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
                                                    <span class="font-semibold text-yellow-800">💡 Offer:</span>
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
                    <p class="text-sm text-gray-400">All checks passed! 🎉</p>
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
