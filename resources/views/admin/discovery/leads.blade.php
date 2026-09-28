@extends('layouts.admin')

@section('title', 'No-Website Leads — Oweru Admin')
@section('page-title', 'No-Website Leads')
@section('page-subtitle', 'Businesses found near you that have no website — call them first')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">No-Website Leads</h1>
            <p class="text-sm text-gray-500 mt-1">
                Businesses discovered on OpenStreetMap that don't have a website yet —
                every one is a potential “we'll build you one” conversation.
            </p>
        </div>
        <a href="{{ route('admin.discovery.index') }}" class="btn-outline text-sm px-4 py-2.5">🔍 Run another discovery</a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Total leads</p>
            <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Not yet contacted</p>
            <p class="text-xl font-extrabold text-green-600 mt-1">{{ $stats['new'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Contacted</p>
            <p class="text-xl font-extrabold text-blue-600 mt-1">{{ $stats['contacted'] }}</p>
        </div>
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-bold text-gray-900">Outreach list</h2>
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" placeholder="Search name or city…" value="{{ request('search') }}" class="rounded-lg border-gray-300 text-sm w-56">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All</option>
                    <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Not yet contacted</option>
                    <option value="contacted" {{ request('status') === 'contacted' ? 'selected' : '' }}>Contacted</option>
                </select>
                <button type="submit" class="btn-outline text-xs px-3 py-2">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">Business</th>
                        <th class="px-6 py-3 text-left">Location</th>
                        <th class="px-6 py-3 text-left">Found</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($leads as $lead)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $lead->business_name }}</div>
                                <div class="text-[11px] text-gray-400">{{ $lead->category }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $lead->city }}
                                @if($lead->lat && $lead->lon)
                                    <a href="https://www.google.com/maps?q={{ $lead->lat }},{{ $lead->lon }}"
                                       target="_blank" rel="noopener noreferrer"
                                       class="ml-1 text-yellow-600 hover:text-yellow-700">📍 map</a>
                                    <div class="text-[11px] text-gray-400">{{ $lead->lat }}, {{ $lead->lon }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">
                                {{ $lead->created_at->diffForHumans() }}
                                <div class="text-[11px] text-gray-400">run #{{ $lead->discovery_run_id }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @if($lead->status === App\Models\DiscoveryLead::STATUS_CONTACTED)
                                    <span class="badge badge-success text-[10px] uppercase">contacted</span>
                                    <div class="text-[11px] text-gray-400 mt-1">{{ $lead->contacted_at?->diffForHumans() }}</div>
                                @else
                                    <span class="badge badge-warning text-[10px] uppercase">new</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if($lead->status === App\Models\DiscoveryLead::STATUS_NEW)
                                    <form method="POST" action="{{ route('admin.discovery.leads.contacted', $lead) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-green-600 hover:text-green-700">✓ Mark contacted</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">
                                No leads yet — run a discovery search on the Website Discovery page.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $leads->links() }}
        </div>
    </div>

</div>
@endsection
