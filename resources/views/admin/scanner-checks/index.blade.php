@extends('layouts.admin')

@section('title', 'Scanner Checks')
@section('page-title', 'Scanner Checks')
@section('page-subtitle', 'Configure and manage scanner check weights and wording')

@section('content')

<div class="flex flex-wrap gap-2 mb-6">
    <a href="{{ route('admin.scanner-checks.create') }}" class="btn-accent text-sm px-4 py-2">
        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Check
    </a>
    <a href="{{ route('admin.scanner-checks.index') }}" class="btn-outline text-sm px-4 py-2">
        All Checks
    </a>
    @foreach($areas as $area)
        <a href="{{ route('admin.scanner-checks.index', ['area' => $area]) }}" class="btn-outline text-sm px-4 py-2">
            {{ $area }}
        </a>
    @endforeach
</div>

<div class="card">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Area</th>
                    <th>Weight</th>
                    <th>Status</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($checks as $check)
                    <tr class="{{ $check->enabled ? '' : 'opacity-50' }}">
                        <td>
                            <div class="font-medium text-sm text-gray-900">{{ $check->name }}</div>
                            <div class="text-xs text-gray-500">{{ $check->slug }}</div>
                        </td>
                        <td>
                            <span class="badge badge-info">{{ $check->area }}</span>
                        </td>
                        <td class="text-center">
                            <span class="font-bold text-gray-900">{{ $check->weight }}</span>
                            <span class="text-xs text-gray-500">pts</span>
                        </td>
                        <td>
                            @if($check->enabled)
                                <span class="badge badge-success">Enabled</span>
                            @else
                                <span class="badge badge-gray">Disabled</span>
                            @endif
                        </td>
                        <td class="text-sm text-gray-600 max-w-xs truncate">{{ $check->description ?? '—' }}</td>
                        <td>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.scanner-checks.edit', $check) }}" class="text-yellow-600 hover:text-yellow-800 text-sm">Edit</a>
                                <form action="{{ route('admin.scanner-checks.destroy', $check) }}" method="POST" onsubmit="return confirm('Delete this scanner check?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-black hover:text-white text-sm">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-400 py-6">No scanner checks found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $checks->links() }}
    </div>
</div>

@endsection
