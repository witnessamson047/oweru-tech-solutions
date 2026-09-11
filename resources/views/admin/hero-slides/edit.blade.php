@extends('layouts.admin')

@section('title', $heroSlide->exists ? 'Edit Slide' : 'Add Slide')
@section('page-title', $heroSlide->exists ? 'Edit Hero Slide' : 'Add Hero Slide')
@section('page-subtitle', $heroSlide->exists ? 'Update this homepage carousel slide' : 'Create a new homepage carousel slide')

@section('content')

<div class="max-w-3xl">
    <form method="POST" action="{{ $heroSlide->exists ? route('admin.hero-slides.update', $heroSlide) : route('admin.hero-slides.store') }}"
        enctype="multipart/form-data" class="card space-y-5">
        @csrf
        @if($heroSlide->exists)
            @method('PUT')
        @endif

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Title / Caption *</label>
            <input type="text" name="title" value="{{ old('title', $heroSlide->title) }}" required
                placeholder="e.g. Website Health Scanner — real diagnostics, instant score"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('title')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Subtitle (optional)</label>
            <input type="text" name="subtitle" value="{{ old('subtitle', $heroSlide->subtitle) }}"
                placeholder="Small secondary line shown under the caption"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('subtitle')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Alt Text * <span class="font-normal text-gray-400">(accessibility description of the image)</span></label>
            <input type="text" name="alt_text" value="{{ old('alt_text', $heroSlide->alt_text) }}" required
                placeholder="e.g. Our team collaborating around a table in the office"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            @error('alt_text')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">
                Image {{ $heroSlide->exists ? '(leave empty to keep current)' : '*' }}
                <span class="font-normal text-gray-400">— JPG/PNG/WebP, max 4MB, landscape works best</span>
            </label>
            @if($heroSlide->exists)
                <img src="{{ asset($heroSlide->image_path) }}" alt="{{ $heroSlide->alt_text }}"
                    class="mb-2 w-full max-w-sm h-44 object-cover rounded-lg border border-gray-200">
            @endif
            <input type="file" name="image" accept="image/jpeg,image/png,image/webp"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm file:mr-3 file:px-3 file:py-1 file:border-0 file:rounded file:bg-yellow-100 file:text-yellow-800 file:text-xs file:font-medium">
            @error('image')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Sort Order *</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $heroSlide->sort_order ?? 0) }}"
                    required min="0" max="9999" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                <p class="text-[11px] text-gray-400 mt-1">Lower numbers appear first in the carousel.</p>
                @error('sort_order')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="flex items-end pb-1">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" {{ old('active', $heroSlide->active ?? true) ? 'checked' : '' }}>
                    Active (visible on the homepage carousel)
                </label>
            </div>
        </div>

        <div class="flex gap-3 pt-2 border-t border-gray-100">
            <button type="submit" class="btn-primary text-sm">{{ $heroSlide->exists ? 'Save Changes' : 'Create Slide' }}</button>
            <a href="{{ route('admin.hero-slides.index') }}" class="btn-outline text-sm">Cancel</a>
        </div>
    </form>
</div>

@endsection
