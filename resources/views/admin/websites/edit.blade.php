@extends('layouts.admin')

@section('title', 'Edit Website')
@section('page-title', 'Edit Website')
@section('page-subtitle', 'Update details for {{ $website->business_name }}')

@section('content')

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.websites.update', $website) }}" class="card space-y-5">
        @csrf
        @method('PUT')

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Business Name *</label>
            <input type="text" name="business_name" value="{{ old('business_name', $website->business_name) }}" required maxlength="255"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('business_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Website URL *</label>
            <input type="text" name="url" inputmode="url" value="{{ old('url', $website->url) }}" required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <p class="text-xs text-gray-400 mt-1">https:// is optional — the address must remain unique.</p>
            @error('url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sector</label>
            <input type="text" name="sector" value="{{ old('sector', $website->sector) }}" maxlength="255"
                placeholder="e.g. Hospitality, Retail, NGO"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('sector')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3 pt-2 border-t border-gray-100">
            <button type="submit" class="btn-primary text-sm">Save Changes</button>
            <a href="{{ route('admin.websites.show', $website) }}" class="btn-outline text-sm">Cancel</a>
        </div>
    </form>
</div>

@endsection
