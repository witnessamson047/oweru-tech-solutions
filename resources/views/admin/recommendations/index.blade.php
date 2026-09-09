@extends('layouts.admin')

@section('title', 'Recommendations')
@section('page-title', 'Recommendation Engine')
@section('page-subtitle', 'Map scanner findings to Oweru service solutions')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    <div class="card">
        <div class="text-sm text-gray-500">Total Recommendations</div>
        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] ?? 0 }}</div>
    </div>
    <div class="card">
        <div class="text-sm text-gray-500">Active Mappings</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['active'] ?? 0 }}</div>
    </div>
    <div class="card">
        <div class="text-sm text-gray-500">Service Categories</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['categories'] ?? 0 }}</div>
    </div>
</div>

{{-- How It Works --}}
<div class="card mb-6 bg-yellow-50 border-yellow-200">
    <div class="flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <div>
            <h4 class="font-semibold text-yellow-900 text-sm">How the Recommendation Engine Works</h4>
            <p class="text-xs text-yellow-700 mt-1">When a check fails during scanning, the system maps the finding to an appropriate Oweru technical service. This bridges the gap between diagnosis and sales by automatically suggesting relevant solutions.</p>
        </div>
    </div>
</div>

{{-- Recommendation Table --}}
<div class="card overflow-hidden">
    <div class="flex items-center justify-between p-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-900">Finding → Solution Mappings</h3>
        <button class="btn-primary text-sm" onclick="document.getElementById('add-modal').classList.remove('hidden')">
            + Add Mapping
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Failed Check</th>
                    <th>Example Finding</th>
                    <th>Business Consequence</th>
                    <th>Recommended Solution</th>
                    <th>Service Category</th>
                    <th>Priority</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recommendations as $rec)
                    <tr>
                        <td>
                            <div class="font-medium text-sm text-gray-900">{{ $rec->check_name }}</div>
                            <span class="badge badge-gray text-[10px]">{{ $rec->area }}</span>
                        </td>
                        <td class="text-sm text-gray-600 max-w-[200px]">{{ Str::limit($rec->finding_example, 80) }}</td>
                        <td class="text-sm text-gray-600 max-w-[200px]">{{ Str::limit($rec->consequence, 80) }}</td>
                        <td class="text-sm text-gray-900 font-medium">{{ $rec->solution }}</td>
                        <td>
                            <span class="badge badge-info text-xs">{{ $rec->service_type }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $rec->priority === 'high' ? 'badge-danger' : ($rec->priority === 'medium' ? 'badge-warning' : 'badge-gray') }}">
                                {{ ucfirst($rec->priority) }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.recommendations.edit', $rec) }}" class="text-yellow-600 hover:underline text-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.recommendations.destroy', $rec) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-black hover:underline text-sm"
                                        onclick="return confirm('Delete this mapping?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-8">No recommendation mappings configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Default Mappings Reference --}}
<div class="mt-6 card">
    <h3 class="font-bold text-gray-900 mb-4">Default Mapping Reference</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <span class="text-black">⚠</span>
            <div>
                <p class="font-medium text-gray-900">Slow mobile performance</p>
                <p class="text-xs text-gray-500">→ Performance optimization / Web development</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <span class="text-black">⚠</span>
            <div>
                <p class="font-medium text-gray-900">Broken links</p>
                <p class="text-xs text-gray-500">→ Website maintenance / Development</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <span class="text-black">⚠</span>
            <div>
                <p class="font-medium text-gray-900">Missing contact path</p>
                <p class="text-xs text-gray-500">→ Website redesign / Contact integration</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <span class="text-black">⚠</span>
            <div>
                <p class="font-medium text-gray-900">Missing privacy/terms</p>
                <p class="text-xs text-gray-500">→ Website improvement / Advisory</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <span class="text-black">⚠</span>
            <div>
                <p class="font-medium text-gray-900">Poor mobile layout</p>
                <p class="text-xs text-gray-500">→ Responsive web development</p>
            </div>
        </div>
        <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
            <span class="text-black">⚠</span>
            <div>
                <p class="font-medium text-gray-900">No payment/booking path</p>
                <p class="text-xs text-gray-500">→ E-commerce / Booking integration</p>
            </div>
        </div>
    </div>
</div>

@endsection
