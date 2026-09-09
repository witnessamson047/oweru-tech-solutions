@extends('layouts.admin')

@section('title', 'Service Packages')
@section('page-title', 'Service Packages')
@section('page-subtitle', 'Manage packages, pricing, and delivery times')

@section('content')

{{-- Actions --}}
<div class="flex items-center justify-between mb-6">
    <div class="text-sm text-gray-500">
        {{ $packages->count() }} packages total
    </div>
    <a href="{{ route('admin.packages.create') }}" class="btn-primary text-sm">+ Add Package</a>
</div>

{{-- Packages by Group --}}
@php
    $groups = [
        'individuals' => 'Individuals & Professionals',
        'sme' => 'Small & Medium Enterprises',
        'corporate' => 'Business & Corporate',
    ];
@endphp

@foreach($groups as $key => $label)
    <div class="mb-8">
        <h3 class="text-lg font-bold text-gray-900 mb-3 flex items-center gap-2">
            <span class="badge badge-info">{{ ucfirst($key) }}</span>
            {{ $label }}
        </h3>
        <div class="card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Price (TZS)</th>
                            <th>Price (USD)</th>
                            <th>Delivery</th>
                            <th>Featured</th>
                            <th>Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($packages->where('group', $key) as $package)
                            <tr>
                                <td class="font-medium text-sm text-gray-900">{{ $package->name }}</td>
                                <td class="text-sm text-gray-600 max-w-[200px]">{{ Str::limit($package->description, 60) }}</td>
                                <td class="text-sm text-gray-700">TZS {{ number_format($package->price_tzs) }}</td>
                                <td class="text-sm text-gray-700">${{ number_format($package->price_usd) }}</td>
                                <td class="text-sm text-gray-700">{{ $package->delivery_days }} days</td>
                                <td>
                                    @if($package->is_featured)
                                        <span class="badge badge-success">Featured</span>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $package->active ? 'badge-success' : 'badge-gray' }}">
                                        {{ $package->active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.packages.edit', $package) }}" class="text-yellow-600 hover:underline text-sm">Edit</a>
                                        <form method="POST" action="{{ route('admin.packages.destroy', $package) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-black hover:underline text-sm"
                                                onclick="return confirm('Delete this package?')">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-gray-400 py-4 text-sm">No packages in this group.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endforeach

@endsection
