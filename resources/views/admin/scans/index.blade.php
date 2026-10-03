@extends('layouts.admin')

@section('title', 'Scans')
@section('page-title', 'Website Scans')
@section('page-subtitle', 'Every scan run, its score and the findings behind it')

@section('content')

@php
    $scoreClass = fn ($score) => $score === null ? 'admin-score-neutral'
        : ($score < 40 ? 'admin-score-critical' : ($score < 60 ? 'admin-score-weak' : ($score < 80 ? 'admin-score-fair' : 'admin-score-strong')));
    $bandVariant = fn ($score) => $score === null ? 'neutral'
        : ($score >= 80 ? 'success' : ($score >= 60 ? 'info' : ($score >= 40 ? 'warning' : 'danger')));
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-admin.stat label="All scans" :value="$stats['total']" icon="scan" />
        <x-admin.stat label="Completed" :value="$stats['completed']" icon="check"
                      :href="route('admin.scans.index', ['status' => 'completed'])" />
        <x-admin.stat label="Failed" :value="$stats['failed']" icon="warning"
                      :href="route('admin.scans.index', ['status' => 'failed'])" />
        <x-admin.stat label="Critical findings" :value="$stats['critical']" icon="ban" />
    </div>

    <x-admin.filters :reset="route('admin.scans.index')" submit-label="Filter">
        <x-admin.search :value="request('search')" placeholder="Business name or URL…" />

        <div class="field">
            <label for="filter-band">Score band</label>
            <select name="band" id="filter-band">
                <option value="">Any band</option>
                @foreach (['Critical' => 'Critical (under 40)', 'Weak' => 'Weak (40–59)', 'Adequate' => 'Adequate (60–79)', 'Strong' => 'Strong (80–100)'] as $value => $text)
                    <option value="{{ $value }}" @selected(request('band') === $value)>{{ $text }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-status">Status</label>
            <select name="status" id="filter-status">
                <option value="">Any status</option>
                @foreach (['completed', 'pending', 'failed'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Scan history</x-slot:title>
        <x-slot:subtitle>{{ $scans->total() }} {{ Str::plural('scan', $scans->total()) }} matching</x-slot:subtitle>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Website</th>
                        <th>
                            <x-admin.sort column="score" label="Score"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" default-direction="desc" />
                        </th>
                        <th>
                            <x-admin.sort column="band" label="Band"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="status" label="Status"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Findings</th>
                        <th>
                            <x-admin.sort column="created_at" label="Scanned"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" default-direction="desc" />
                        </th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($scans as $scan)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $scan->website->business_name ?? 'Unknown site' }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-[16rem]">{{ $scan->url }}</div>
                            </td>
                            <td>
                                <span class="admin-score {{ $scoreClass($scan->score) }} admin-tabular">
                                    {{ $scan->score ?? '–' }}
                                </span>
                            </td>
                            <td>
                                @if ($scan->band)
                                    <x-admin.badge :variant="$bandVariant($scan->score)">{{ $scan->band }}</x-admin.badge>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td>
                                <x-admin.badge :variant="$scan->status === 'completed' ? 'success' : ($scan->status === 'failed' ? 'danger' : 'warning')">
                                    {{ ucfirst($scan->status) }}
                                </x-admin.badge>
                            </td>
                            <td class="admin-tabular text-gray-700">{{ $scan->results_count ?? $scan->results->count() }}</td>
                            <td>
                                <span class="text-gray-700">{{ $scan->created_at->diffForHumans() }}</span>
                                <span class="block text-xs text-gray-400">{{ $scan->created_at->format('j M Y') }}</span>
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.scans.show', $scan)" icon="eye" title="Open scan">Open</x-admin.action>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="7" icon="scan" title="No scans found"
                                       text="Once a website is scanned its results land here. Try clearing the filters, or run a scan from the websites list." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$scans" label="scans" />
    </x-admin.card>
</div>

@endsection