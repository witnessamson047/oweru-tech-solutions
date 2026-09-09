@extends('layouts.admin')

@section('title', 'Enquiries')
@section('page-title', 'Enquiries')
@section('page-subtitle', 'Manage and track all client enquiries')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="card">
        <div class="text-sm text-gray-500">Total</div>
        <div class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</div>
    </div>
    <div class="card">
        <div class="text-sm text-gray-500">New</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['new'] }}</div>
    </div>
    <div class="card">
        <div class="text-sm text-gray-500">Won</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['won'] }}</div>
    </div>
    <div class="card">
        <div class="text-sm text-gray-500">This Month</div>
        <div class="text-2xl font-bold text-yellow-600">{{ $stats['this_month'] }}</div>
    </div>
</div>

{{-- Filters --}}
<div class="card mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, email, business..."
                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Stage</label>
            <select name="stage" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                <option value="">All Stages</option>
                @foreach(['new', 'qualified', 'diagnostic_paid', 'proposal_sent', 'won', 'lost'] as $stage)
                    <option value="{{ $stage }}" {{ request('stage') === $stage ? 'selected' : '' }}>
                        {{ str_replace('_', ' ', ucfirst($stage)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Source</label>
            <select name="source" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-yellow-500">
                <option value="">All Sources</option>
                <option value="website" {{ request('source') === 'website' ? 'selected' : '' }}>Website</option>
                <option value="scanner" {{ request('source') === 'scanner' ? 'selected' : '' }}>Scanner</option>
                <option value="referral" {{ request('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                <option value="manual" {{ request('source') === 'manual' ? 'selected' : '' }}>Manual</option>
            </select>
        </div>
        <button type="submit" class="btn-primary text-sm">Filter</button>
    </form>
</div>

{{-- Enquiries Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Contact</th>
                    <th>Business</th>
                    <th>Package</th>
                    <th>Stage</th>
                    <th>Budget</th>
                    <th>Source</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($enquiries as $enquiry)
                    <tr class="pipeline-row" data-stage="{{ $enquiry->stage }}">
                        <td>
                            <div class="font-medium text-gray-900 text-sm">{{ $enquiry->name }}</div>
                            <div class="text-xs text-gray-500">{{ $enquiry->email }}</div>
                        </td>
                        <td class="text-sm text-gray-700">{{ $enquiry->business_name }}</td>
                        <td class="text-sm text-gray-700">{{ $enquiry->package_name ?? '-' }}</td>
                        <td>
                            <span class="badge stage-{{ $enquiry->stage === 'diagnostic_paid' ? 'diagnostic' : $enquiry->stage }}">
                                {{ str_replace('_', ' ', ucfirst($enquiry->stage)) }}
                            </span>
                        </td>
                        <td class="text-sm text-gray-700">{{ str_replace('_', ' ', ucfirst($enquiry->budget_range ?? '')) }}</td>
                        <td>
                            <span class="badge badge-{{ $enquiry->source === 'scanner' ? 'info' : 'gray' }}">
                                {{ ucfirst($enquiry->source ?? 'direct') }}
                            </span>
                        </td>
                        <td class="text-sm text-gray-500">{{ $enquiry->created_at->diffForHumans() }}</td>
                        <td>
                            <a href="{{ route('admin.enquiries.show', $enquiry) }}" class="text-yellow-600 hover:underline text-sm">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-gray-400 py-8">No enquiries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if(method_exists($enquiries, 'links'))
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $enquiries->links() }}
        </div>
    @endif
</div>

@endsection
