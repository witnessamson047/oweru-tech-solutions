@extends('layouts.admin')

@section('title', 'Hero Slides')
@section('page-title', 'Homepage Hero Slides')
@section('page-subtitle', 'Manage the image carousel on the public homepage')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="text-sm text-gray-500">
        {{ $heroSlides->count() }} slides · {{ $heroSlides->where('active', true)->count() }} active
    </div>
    <a href="{{ route('admin.hero-slides.create') }}" class="btn-primary text-sm">+ Add Slide</a>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Title / Caption</th>
                    <th>Order</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($heroSlides as $slide)
                    <tr>
                        <td>
                            <img src="{{ asset($slide->image_path) }}" alt="{{ $slide->alt_text }}"
                                class="w-28 h-16 object-cover rounded-lg border border-gray-200">
                        </td>
                        <td class="max-w-[360px]">
                            <p class="font-medium text-sm text-gray-900">{{ $slide->title }}</p>
                            @if($slide->subtitle)
                                <p class="text-xs text-gray-500">{{ $slide->subtitle }}</p>
                            @endif
                            <p class="text-[11px] text-gray-400 mt-0.5 truncate">{{ $slide->image_path }}</p>
                        </td>
                        <td class="text-sm text-gray-700">{{ $slide->sort_order }}</td>
                        <td>
                            <span class="badge {{ $slide->active ? 'badge-success' : 'badge-gray' }}">
                                {{ $slide->active ? 'Active' : 'Hidden' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.hero-slides.edit', $slide) }}" class="text-yellow-600 hover:underline text-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.hero-slides.destroy', $slide) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-black hover:underline text-sm"
                                        onclick="return confirm('Delete this slide and its image?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-400 py-8">
                            No slides configured — the homepage will show default slides.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
