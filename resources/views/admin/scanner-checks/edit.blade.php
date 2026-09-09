@extends('layouts.admin')

@section('title', 'Edit Scanner Check')
@section('page-title', 'Edit Scanner Check')
@section('page-subtitle', 'Update scanner check configuration')

@section('content')

<div class="card max-w-2xl">
    <form action="{{ route('admin.scanner-checks.update', $scannerCheck) }}" method="POST">
        @csrf
        @method('PATCH')

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Check Name *</label>
                <input type="text" name="name" value="{{ old('name', $scannerCheck->name) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('name') border-gray-600 @enderror"
                    placeholder="e.g., SSL Certificate Valid">
                @error('name')
                    <p class="mt-1 text-xs text-black">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug *</label>
                <input type="text" name="slug" value="{{ old('slug', $scannerCheck->slug) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('slug') border-gray-600 @enderror"
                    placeholder="e.g., ssl-valid">
                @error('slug')
                    <p class="mt-1 text-xs text-black">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Lowercase, hyphen-separated identifier.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Area *</label>
                <select name="area" class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('area') border-gray-600 @enderror">
                    <option value="">Select area</option>
                    @foreach($areas as $area)
                        <option value="{{ $area }}" {{ old('area', $scannerCheck->area) === $area ? 'selected' : '' }}>{{ $area }}</option>
                    @endforeach
                </select>
                @error('area')
                    <p class="mt-1 text-xs text-black">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Weight (Points) *</label>
                <input type="number" name="weight" value="{{ old('weight', $scannerCheck->weight) }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('weight') border-gray-600 @enderror"
                    min="1" max="100" placeholder="e.g., 10">
                @error('weight')
                    <p class="mt-1 text-xs text-black">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Points awarded when this check passes.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="2"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('description') border-gray-600 @enderror"
                    placeholder="Brief description of what this check measures">{{ old('description', $scannerCheck->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-xs text-black">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Wording When Passed</label>
                <textarea name="wording_pass" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('wording_pass') border-gray-600 @enderror"
                    placeholder="Message shown to client when this check passes">{{ old('wording_pass', $scannerCheck->wording_pass) }}</textarea>
                @error('wording_pass')
                    <p class="mt-1 text-xs text-black">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Wording When Failed</label>
                <textarea name="wording_fail" rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg @error('wording_fail') border-gray-600 @enderror"
                    placeholder="Message shown to client when this check fails">{{ old('wording_fail', $scannerCheck->wording_fail) }}</textarea>
                @error('wording_fail')
                    <p class="mt-1 text-xs text-black">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" name="enabled" value="1" {{ old('enabled', $scannerCheck->enabled) ? 'checked' : '' }}
                    class="w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500">
                <label class="text-sm text-gray-700">Enable this check</label>
            </div>
        </div>

        <div class="mt-6 flex items-center gap-3">
            <a href="{{ route('admin.scanner-checks.index') }}" class="btn-outline">Cancel</a>
            <button type="submit" class="btn-accent">Update Check</button>
        </div>
    </form>
</div>

@endsection
