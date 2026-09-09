@extends('layouts.admin')

@section('title', $exclusion->exists ? 'Edit Exclusion' : 'Add Exclusion')
@section('page-title', $exclusion->exists ? 'Edit Exclusion' : 'Add Exclusion')
@section('page-subtitle', $exclusion->exists ? 'Update exclusion' : 'Add a new package exclusion')

@section('content')

<div class="max-w-2xl">
    <form method="POST" action="{{ $exclusion->exists ? route('admin.package-exclusions.update', $exclusion) : route('admin.package-exclusions.store') }}">
        @csrf
        @if($exclusion->exists) @method('PUT') @endif

        <div class="card">
            <h3 class="font-bold text-gray-900 mb-4">Exclusion Details</h3>

            <div class="space-y-4">
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <input type="text" name="description" id="description" value="{{ old('description', $exclusion->description) }}"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-sm"
                        placeholder="e.g., Custom ERP integration">
                    @error('description') <p class="text-black text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="details" class="block text-sm font-medium text-gray-700 mb-1">Details</label>
                    <textarea name="details" id="details" rows="3"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-sm"
                        placeholder="Additional information about what's excluded...">{{ old('details', $exclusion->details) }}</textarea>
                </div>

                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Sort Order *</label>
                    <input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $exclusion->sort_order ?? 0) }}" min="0"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 text-sm">
                </div>

                <div class="flex items-center gap-3">
                    <input type="checkbox" name="active" id="active" value="1"
                        {{ old('active', $exclusion->active ?? true) ? 'checked' : '' }}
                        class="h-4 w-4 text-yellow-600 border-gray-300 rounded">
                    <label for="active" class="text-sm text-gray-700">Active</label>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="btn-primary">
                {{ $exclusion->exists ? 'Update Exclusion' : 'Create Exclusion' }}
            </button>
            @if($exclusion->exists)
                <a href="{{ route('admin.package-exclusions.index') }}" class="btn-outline">Cancel</a>
            @endif
        </div>
    </form>
</div>

@endsection
