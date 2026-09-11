@extends('layouts.admin')

@section('title', 'Website Details')
@section('page-title', $website->business_name)
@section('page-subtitle', $website->url)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Main Content --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Website Info --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Website Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Business Name:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $website->business_name }}</span>
                </div>
                <div>
                    <span class="text-gray-500">URL:</span>
                    <a href="{{ $website->url }}" target="_blank" class="font-medium text-yellow-600 ml-2">{{ $website->url }}</a>
                </div>
                <div>
                    <span class="text-gray-500">Sector:</span>
                    <span class="font-medium text-gray-900 ml-2">{{ $website->sector ?? 'Not specified' }}</span>
                </div>
                <div>
                    <span class="text-gray-500">Status:</span>
                    <span class="badge {{ $website->exclusion_status === 'excluded' ? 'badge-danger' : 'badge-success' }}">
                        {{ ucfirst($website->status ?? 'active') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Scan History --}}
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-black flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Scan History
                </h3>
                <button onclick="scanWebsite({{ $website->id }})" class="btn-accent text-sm">
                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m0 0H1m0 0a8.000 8.000 0 00-1.029 1.68M15.58 16H9m3.366 3.366A8.002 8.002 0 0021.42 20H15.58m0 0a8.001 8.001 0 01-9.033-2M1 13.58V19m0 0a8.003 8.003 0 005.075 2.29l.075.072M10.5 18.5H9"/></svg>
                    Run Scan
                </button>
            </div>
            <div class="space-y-3">
                @forelse($website->scans as $scan)
                    <a href="{{ route('admin.scans.show', $scan) }}"
                        class="flex items-center gap-4 p-4 rounded-lg border border-gray-100 hover:border-yellow-200 hover:bg-yellow-50/50 transition">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg
                            {{ $scan->score < 40 ? 'bg-black text-white' : ($scan->score < 60 ? 'bg-yellow-800 text-white' : ($scan->score < 80 ? 'bg-yellow-500 text-black' : 'bg-yellow-300 text-black')) }}">
                            {{ $scan->score }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="badge {{ $scan->score >= 80 ? 'badge-success' : ($scan->score >= 60 ? 'badge-info' : ($scan->score >= 40 ? 'badge-warning' : 'badge-danger')) }}">
                                    {{ $scan->band }}
                                </span>
                                <span class="text-xs text-gray-500">{{ $scan->started_at?->format('d M Y H:i') ?? $scan->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                {{ $scan->results->count() }} checks performed
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @empty
                    <div class="text-center text-gray-400 py-8">No scans performed yet. Click "New Scan" to start.</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-6">

        {{-- Score Trend --}}
        <div class="card text-center">
            <h3 class="font-bold text-gray-900 mb-3">Latest Score</h3>
            @if($website->latestScan)
                @php $score = $website->latestScan->score; @endphp
                <div class="w-24 h-24 rounded-full border-4 mx-auto flex items-center justify-center font-extrabold text-2xl
                    {{ $score < 40 ? 'border-black text-white bg-gray-100' : ($score < 60 ? 'border-yellow-600 text-yellow-800 bg-yellow-50' : ($score < 80 ? 'border-yellow-500 text-yellow-800 bg-yellow-50' : 'border-yellow-400 text-yellow-800 bg-yellow-50')) }}">
                    {{ $score }}
                </div>
                <span class="badge {{ $score >= 80 ? 'badge-success' : ($score >= 60 ? 'badge-info' : ($score >= 40 ? 'badge-warning' : 'badge-danger')) }} mt-3">
                    {{ $website->latestScan->band }}
                </span>
            @else
                <div class="w-24 h-24 rounded-full border-4 border-gray-200 mx-auto flex items-center justify-center text-gray-400">?</div>
                <p class="text-sm text-gray-400 mt-2">No scans yet</p>
            @endif
        </div>

        {{-- Contact --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Linked Enquiry</h3>
            @if($website->enquiry)
                <a href="{{ route('admin.enquiries.show', $website->enquiry) }}" class="text-sm text-yellow-600 hover:underline">
                    {{ $website->enquiry->name }} ({{ $website->enquiry->business_name }})
                </a>
            @else
                <p class="text-sm text-gray-400">No linked enquiry.</p>
            @endif
        </div>

        {{-- Actions --}}
        <div class="card">
            <h3 class="font-bold text-gray-900 mb-3">Actions</h3>
            <div class="space-y-2">
                <button onclick="scanWebsite({{ $website->id }})" class="btn-accent text-sm w-full justify-center">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m0 0H1m0 0a8.000 8.000 0 00-1.029 1.68M15.58 16H9m3.366 3.366A8.002 8.002 0 0021.42 20H15.58m0 0a8.001 8.001 0 01-9.033-2M1 13.58V19m0 0a8.003 8.003 0 005.075 2.29l.075.072M10.5 18.5H9"/></svg>
                    Run Internal Scan
                </button>
                <a href="{{ $website->url }}" target="_blank" class="btn-outline text-sm w-full justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                    Visit Website
                </a>
                @if($website->exclusion_status !== 'excluded')
                    <form method="POST" action="{{ route('admin.websites.exclude', $website) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn-danger text-sm w-full justify-center"
                            onclick="return confirm('Exclude this website from scanning?')">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728A9 9 0 015.636 5.636"/></svg>
                            Exclude from Scanning
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
