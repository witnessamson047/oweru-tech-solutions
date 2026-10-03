@extends('layouts.admin')

@section('title', 'Websites')
@section('page-title', 'Websites')
@section('page-subtitle', 'Every site in the portfolio and its latest scan score')

@section('content')

@php
    $scoreVariant = fn ($score) => $score === null ? 'neutral'
        : ($score >= 80 ? 'success' : ($score >= 60 ? 'info' : ($score >= 40 ? 'warning' : 'danger')));
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Tracked websites" :value="$stats['total']" icon="globe" />
        <x-admin.stat label="Active" :value="$stats['active']" icon="check"
                      :href="route('admin.websites.index', ['status' => 'active'])" />
        <x-admin.stat label="Excluded" :value="$stats['excluded']" icon="ban"
                      :href="route('admin.websites.index', ['status' => 'excluded'])" />
    </div>

    <x-admin.note tone="info" title="What lives here">
        A website is anything you scan — a client site, a prospect, or a lead you found
        through discovery. Batch-scanning queues a scan for every site that matches the
        current filters, so filter first if you only want to scan a subset.
    </x-admin.note>

    <x-admin.filters :reset="route('admin.websites.index')" submit-label="Filter">
        <x-admin.search :value="request('search')" placeholder="Business name or URL…" />

        <div class="field">
            <label for="filter-status">Status</label>
            <select name="status" id="filter-status">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="excluded" @selected(request('status') === 'excluded')>Excluded</option>
            </select>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Websites</x-slot:title>
        <x-slot:subtitle>{{ $websites->total() }} {{ Str::plural('website', $websites->total()) }} matching</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.websites.create') }}" class="admin-btn">
                <x-admin.icon name="plus" class="w-4 h-4" />
                Add website
            </a>
            @if ($websites->total() > 0)
                <input type="hidden" id="batch-scan-count" value="{{ $websites->total() }}">
                <button type="button" id="batch-scan-btn" onclick="batchScanWebsites()" class="admin-btn-ghost">
                    <x-admin.icon name="scan" class="w-4 h-4" />
                    Scan all ({{ $websites->total() }})
                </button>
            @endif
        </x-slot:actions>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort column="business_name" label="Business"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>URL</th>
                        <th>
                            <x-admin.sort column="sector" label="Sector"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Last score</th>
                        <th>Status</th>
                        <th>
                            <x-admin.sort column="scans_count" label="Scans"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($websites as $website)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $website->business_name }}</div>
                            </td>
                            <td>
                                <a href="{{ $website->url }}" target="_blank" rel="noopener noreferrer"
                                   class="admin-inline-link max-w-[16rem] inline-block truncate align-bottom">
                                    {{ Str::limit(preg_replace('#^https?://#', '', $website->url), 40) }}
                                </a>
                            </td>
                            <td class="text-gray-600">{{ $website->sector ?: '—' }}</td>
                            <td>
                                @if ($website->latestScan?->score !== null)
                                    <x-admin.badge :variant="$scoreVariant($website->latestScan->score)">
                                        {{ $website->latestScan->score }}/100
                                    </x-admin.badge>
                                @else
                                    <span class="text-xs text-gray-400">Not scanned</span>
                                @endif
                            </td>
                            <td>
                                <x-admin.badge :variant="$website->exclusion_status === 'excluded' ? 'danger' : 'success'">
                                    {{ ucfirst($website->status ?? 'active') }}
                                </x-admin.badge>
                            </td>
                            <td class="admin-tabular text-gray-700">{{ $website->scans_count ?? 0 }}</td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.websites.show', $website)" icon="eye" title="Open">Open</x-admin.action>
                                    <x-admin.action href="#" icon="scan" title="Scan now"
                                                    onclick="event.preventDefault(); scanWebsite({{ $website->id }});" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="7" icon="globe" title="No websites found"
                                       text="Add a website to start tracking its health, or clear the filters to see the full portfolio.">
                            <a href="{{ route('admin.websites.create') }}" class="admin-btn">
                                <x-admin.icon name="plus" class="w-4 h-4" /> Add the first website
                            </a>
                        </x-admin.empty>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$websites" label="websites" />
    </x-admin.card>
</div>

@endsection