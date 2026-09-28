@extends('layouts.admin')

@section('title', 'Add Website')
@section('page-title', 'Add Website')
@section('page-subtitle', 'Track a new website for scanning and monitoring')

@section('content')

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.websites.store') }}" class="card space-y-5">
        @csrf

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Business Name *</label>
            <input type="text" name="business_name" value="{{ old('business_name') }}" required maxlength="255"
                placeholder="e.g. Demo Cafe"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('business_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Website URL *</label>
            <input type="text" name="url" inputmode="url" value="{{ old('url') }}" required
                placeholder="e.g. abc.co.tz — https:// is optional"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <p class="text-xs text-gray-400 mt-1">Include https:// — must be a unique, valid URL.</p>
            @error('url')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Sector</label>
            <input type="text" name="sector" value="{{ old('sector') }}" maxlength="255"
                placeholder="e.g. Hospitality, Retail, NGO"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('sector')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="flex gap-3 pt-2 border-t border-gray-100">
            <button type="submit" class="btn-primary text-sm">Add Website</button>
            <a href="{{ route('admin.websites.index') }}" class="btn-outline text-sm">Cancel</a>
        </div>
    </form>
</div>

@endsection
