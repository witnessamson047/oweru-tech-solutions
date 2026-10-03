@extends('layouts.admin')

@section('title', 'No-Website Leads')
@section('page-title', 'No-Website Leads')
@section('page-subtitle', 'Businesses found with no website — the warmest “we’ll build you one” calls')

@section('content')

@php
    use App\Models\DiscoveryLead;
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Total leads" :value="$stats['total']" icon="target" />
        <x-admin.stat label="Not yet contacted" :value="$stats['new']" icon="bell"
                      :href="route('admin.discovery.leads', ['status' => 'new'])" />
        <x-admin.stat label="Contacted" :value="$stats['contacted']" icon="check-circle"
                      :href="route('admin.discovery.leads', ['status' => 'contacted'])" />
    </div>

    <x-admin.note tone="info" title="Why these matter">
        A business with no website is the easiest sale to open — there is nothing to
        replace, only something to build. Mark a lead contacted so the team does not
        call twice.
    </x-admin.note>

    <x-admin.card :padded="false">
        <x-slot:title>Outreach list</x-slot:title>
        <x-slot:subtitle>{{ $leads->total() }} {{ Str::plural('lead', $leads->total()) }} matching</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.discovery.index') }}" class="admin-btn-ghost">
                <x-admin.icon name="search" class="w-4 h-4" /> Run discovery
            </a>
        </x-slot:actions>

        <form method="GET" class="admin-filters" role="search">
            <x-admin.search :value="request('search')" placeholder="Search name or city…" />

            <div class="field">
                <label for="filter-status">Status</label>
                <select name="status" id="filter-status">
                    <option value="">All leads</option>
                    <option value="new" @selected(request('status') === 'new')>Not yet contacted</option>
                    <option value="contacted" @selected(request('status') === 'contacted')>Contacted</option>
                </select>
            </div>

            <div class="spacer"></div>
            <div class="flex items-center gap-2">
                <button type="submit" class="admin-btn">
                    <x-admin.icon name="filter" class="w-4 h-4" /> Filter
                </button>
                @if (request()->query())
                    <a href="{{ route('admin.discovery.leads') }}" class="admin-btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Location</th>
                        <th>Found</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $lead->business_name }}</div>
                                <div class="text-xs text-gray-500">{{ $lead->category }}</div>
                            </td>
                            <td>
                                <div class="text-gray-700">{{ $lead->city }}</div>
                                @if ($lead->lat && $lead->lon)
                                    <a href="https://www.google.com/maps?q={{ $lead->lat }},{{ $lead->lon }}"
                                       target="_blank" rel="noopener noreferrer" class="admin-inline-link text-xs">map</a>
                                    <div class="text-xs text-gray-400">{{ $lead->lat }}, {{ $lead->lon }}</div>
                                @endif
                            </td>
                            <td class="whitespace-nowrap">
                                <span class="text-gray-700">{{ $lead->created_at->diffForHumans() }}</span>
                                <span class="block text-xs text-gray-400">run #{{ $lead->discovery_run_id }}</span>
                            </td>
                            <td>
                                @if ($lead->status === DiscoveryLead::STATUS_CONTACTED)
                                    <x-admin.badge variant="success">Contacted</x-admin.badge>
                                    <div class="text-xs text-gray-400 mt-1">{{ $lead->contacted_at?->diffForHumans() }}</div>
                                @else
                                    <x-admin.badge variant="warning">New</x-admin.badge>
                                @endif
                            </td>
                            <td class="col-actions">
                                @if ($lead->status === DiscoveryLead::STATUS_NEW)
                                    <div class="admin-actions">
                                        <x-admin.action :action="route('admin.discovery.leads.contacted', $lead)" method="POST"
                                                        icon="check" variant="primary" title="Mark this lead as contacted">
                                            Contacted
                                        </x-admin.action>
                                    </div>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="5" icon="target" title="No leads found"
                                       text="Run a discovery search on the Website Discovery page — businesses without a website will appear here for outreach." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$leads" label="leads" />
    </x-admin.card>
</div>

@endsection