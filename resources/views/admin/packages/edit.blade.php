@extends('layouts.admin')

@section('title', 'Edit Package')
@section('page-title', 'Edit Service Package')
@section('page-subtitle', $package->name)

@section('content')

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.packages.update', $package) }}" class="card space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Package Name *</label>
            <input type="text" name="name" value="{{ old('name', $package->name) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Customer Group *</label>
                <select name="group" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    @foreach($groups as $key => $label)
                        <option value="{{ $key }}" {{ old('group', $package->group) === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('group')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Service Line</label>
                <select name="service_line_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">— None —</option>
                    @foreach($serviceLines as $line)
                        <option value="{{ $line->id }}" {{ old('service_line_id', $package->service_line_id) == $line->id ? 'selected' : '' }}>
                            {{ $line->icon }} {{ $line->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Description</label>
            <textarea name="description" rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">{{ old('description', $package->description) }}</textarea>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Price TZS *</label>
                <input type="number" name="price_tzs" value="{{ old('price_tzs', $package->price_tzs) }}" required min="0" step="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                @error('price_tzs')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Price USD *</label>
                <input type="number" name="price_usd" value="{{ old('price_usd', $package->price_usd) }}" required min="0" step="0.01"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                @error('price_usd')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Delivery (days) *</label>
                <input type="number" name="delivery_days" value="{{ old('delivery_days', $package->delivery_days) }}" required min="1"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                @error('delivery_days')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="flex gap-6">
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="is_featured" value="0">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $package->is_featured) ? 'checked' : '' }}>
                Featured (Recommended badge)
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" {{ old('active', $package->active) ? 'checked' : '' }}>
                Active (visible on public site)
            </label>
        </div>

        <div class="flex gap-3 pt-2 border-t border-gray-100">
            <button type="submit" class="btn-primary text-sm">Save Changes</button>
            <a href="{{ route('admin.packages.index') }}" class="btn-outline text-sm">Cancel</a>
        </div>
    </form>
</div>

@endsection
