@extends('layouts.admin')

@section('title', 'Package Exclusions')
@section('page-title', 'Package Exclusions')
@section('page-subtitle', 'Items not included in standard service packages')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="text-sm text-gray-500">
        {{ $exclusions->count() }} exclusions
    </div>
    <a href="{{ route('admin.package-exclusions.create') }}" class="btn-primary text-sm">+ Add Exclusion</a>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Details</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exclusions as $exclusion)
                    <tr>
                        <td class="text-sm text-gray-400">{{ $exclusion->sort_order }}</td>
                        <td class="font-medium text-sm text-gray-900">{{ $exclusion->description }}</td>
                        <td class="text-sm text-gray-600 max-w-[250px]">{{ Str::limit($exclusion->details ?? '—', 60) }}</td>
                        <td>
                            <span class="badge {{ $exclusion->active ? 'badge-success' : 'badge-gray' }}\">
                                {{ $exclusion->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.package-exclusions.edit', $exclusion) }}" class="text-yellow-600 hover:underline text-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.package-exclusions.destroy', $exclusion) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-black hover:underline text-sm" onclick="return confirm('Delete this exclusion?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-400 py-8">No exclusions configured.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
