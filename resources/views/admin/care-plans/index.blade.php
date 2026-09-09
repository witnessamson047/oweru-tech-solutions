@extends('layouts.admin')

@section('title', 'Care Plans')
@section('page-title', 'Monthly Care Plans')
@section('page-subtitle', 'Manage recurring maintenance and support plans')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div class="text-sm text-gray-500">
        {{ $carePlans->count() }} care plans total
    </div>
    <a href="{{ route('admin.care-plans.create') }}" class="btn-primary text-sm">+ Add Care Plan</a>
</div>

<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Price (TZS)</th>
                    <th>Price (USD)</th>
                    <th>Featured</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($carePlans as $plan)
                    <tr>
                        <td class="font-medium text-sm text-gray-900">{{ $plan->name }}</td>
                        <td class="text-sm text-gray-600 max-w-[300px]">{{ Str::limit($plan->description, 80) }}</td>
                        <td class="text-sm text-gray-700">TZS {{ number_format($plan->price_tzs) }}/mo</td>
                        <td class="text-sm text-gray-700">${{ number_format($plan->price_usd) }}/mo</td>
                        <td>
                            @if($plan->is_featured)
                                <span class="badge badge-success">Featured</span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $plan->active ? 'badge-success' : 'badge-gray' }}">
                                {{ $plan->active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>
                            <div class="flex gap-2">
                                <a href="{{ route('admin.care-plans.edit', $plan) }}" class="text-yellow-600 hover:underline text-sm">Edit</a>
                                <form method="POST" action="{{ route('admin.care-plans.destroy', $plan) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-black hover:underline text-sm"
                                        onclick="return confirm('Delete this care plan?')">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-gray-400 py-8">No care plans configured yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
