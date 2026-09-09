@extends('layouts.admin')

@section('title', 'Scans')
@section('page-title', 'Website Scans')
@section('page-subtitle', 'All scan results and history')

@section('content')

{{-- Filters --}}
<div class="card mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Business name or URL..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Score Band</label>
            <select name="band" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All Bands</option>
                <option value="Critical" {{ request('band') === 'Critical' ? 'selected' : '' }}>Critical (Under 40)</option>
                <option value="Weak" {{ request('band') === 'Weak' ? 'selected' : '' }}>Weak (40-59)</option>
                <option value="Adequate" {{ request('band') === 'Adequate' ? 'selected' : '' }}>Adequate (60-79)</option>
                <option value="Strong" {{ request('band') === 'Strong' ? 'selected' : '' }}>Strong (80-100)</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Status</label>
            <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <option value="">All</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </form>
</div>

{{-- Scans Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Website</th>
                    <th>Score</th>
                    <th>Band</th>
                    <th>Status</th>
                    <th>Findings</th>
                    <th>Scanned</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($scans as $scan)
                    <tr>
                        <td>
                            <div class="font-medium text-sm text-gray-900">{{ $scan->website->business_name ?? 'Unknown' }}</div>
                            <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ $scan->url }}</div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center font-bold text-sm
                                    {{ ($scan->score ?? 0) < 40 ? 'bg-black text-white' : (($scan->score ?? 0) < 60 ? 'bg-yellow-800 text-white' : (($scan->score ?? 0) < 80 ? 'bg-yellow-500 text-black' : 'bg-yellow-300 text-black')) }}">
                                    {{ $scan->score ?? '-' }}
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge {{ ($scan->score ?? 0) >= 80 ? 'badge-success' : (($scan->score ?? 0) >= 60 ? 'badge-info' : (($scan->score ?? 0) >= 40 ? 'badge-warning' : 'badge-danger')) }}">
                                {{ $scan->band ?? '-' }}
                            </span>
                        </td>
                        <td>
                            <span class="badge {{ $scan->status === 'completed' ? 'badge-success' : ($scan->status === 'failed' ? 'badge-danger' : 'badge-warning') }}">
                                {{ ucfirst($scan->status) }}
                            </span>
                        </td>
                        <td class="text-sm text-gray-700">{{ $scan->results_count ?? $scan->results->count() ?? 0 }}</td>
                        <td class="text-xs text-gray-500">{{ $scan->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.scans.show', $scan) }}" class="text-yellow-600 hover:underline text-sm">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-8">No scans found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($scans, 'links'))
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $scans->links() }}
        </div>
    @endif
</div>

@endsection
