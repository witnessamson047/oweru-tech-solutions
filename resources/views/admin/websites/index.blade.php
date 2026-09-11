@extends('layouts.admin')

@section('title', 'Websites')
@section('page-title', 'Websites')
@section('page-subtitle', 'Manage websites being tracked and scanned')

@section('content')

{{-- Actions --}}
<div class="flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search websites..."
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="excluded" {{ request('status') === 'excluded' ? 'selected' : '' }}>Excluded</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
            <button type="submit" class="btn-primary text-sm">Filter</button>
        </form>
    </div>
    <div class="flex items-center gap-3">
        <span class="text-sm text-gray-500 hidden md:inline">Staff Actions:</span>
        <a href="{{ route('admin.websites.create') }}" class="btn-primary text-sm">
            + Add Website
        </a>
        @php $batchCount = $websites->total(); @endphp
        @if($batchCount > 0)
            <input type="hidden" id="batch-scan-count" value="{{ $batchCount }}">
            <button id="batch-scan-btn" onclick="batchScanWebsites()" class="btn-outline text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Scan All ({{ $batchCount }})
            </button>
        @endif
    </div>
</div>

{{-- Quick Scan Banner --}}
<div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="font-semibold text-black text-sm">Staff Scanner</p>
            <p class="text-xs text-gray-500">Run internal scans on any website in your portfolio</p>
        </div>
    </div>
    <a href="{{ route('admin.scans.index') }}" class="text-yellow-600 hover:text-yellow-700 text-sm font-medium">
        View All Scans →
    </a>
</div>

{{-- Websites Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Business Name</th>
                    <th>URL</th>
                    <th>Sector</th>
                    <th>Last Score</th>
                    <th>Status</th>
                    <th>Scans</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($websites as $website)
                    <tr>
                        <td>
                            <div class="font-medium text-sm text-gray-900">{{ $website->business_name }}</div>
                        </td>
                        <td>
                            <a href="{{ $website->url }}" target="_blank" class="text-sm text-yellow-600 hover:underline">
                                {{ Str::limit($website->url, 40) }}
                            </a>
                        </td>
                        <td class="text-sm text-gray-700">{{ $website->sector ?? '-' }}</td>
                        <td>
                            @if($website->latestScan)
                                <span class="badge {{ $website->latestScan->score >= 80 ? 'badge-success' : ($website->latestScan->score >= 60 ? 'badge-info' : ($website->latestScan->score >= 40 ? 'badge-warning' : 'badge-danger')) }}">
                                    {{ $website->latestScan->score }}/100
                                </span>
                            @else
                                <span class="text-xs text-gray-400">Not scanned</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $website->exclusion_status === 'excluded' ? 'badge-danger' : 'badge-success' }}">
                                {{ ucfirst($website->status ?? 'active') }}
                            </span>
                        </td>
                        <td class="text-sm text-gray-700">{{ $website->scans_count ?? 0 }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.websites.show', $website) }}" class="text-yellow-600 hover:underline text-sm">View</a>
                                <button onclick="scanWebsite({{ $website->id }})" class="text-yellow-600 hover:underline text-sm">Scan</button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-8">No websites tracked yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($websites, 'links'))
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $websites->links() }}
        </div>
    @endif
</div>

@endsection
