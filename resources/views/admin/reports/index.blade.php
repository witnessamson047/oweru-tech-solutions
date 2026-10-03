@extends('layouts.admin')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'One-page PDFs generated from completed scans')

@section('content')

@php
    $scoreVariant = fn ($score) => $score === null ? 'neutral'
        : ($score >= 80 ? 'success' : ($score >= 60 ? 'info' : ($score >= 40 ? 'warning' : 'danger')));
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Reports generated" :value="$stats['total']" icon="document-text" />
        <x-admin.stat label="Total downloads" :value="number_format($stats['downloads'])" icon="download" />
        <x-admin.stat label="This month" :value="$stats['this_month']" icon="calendar" />
    </div>

    <x-admin.note tone="info" title="Where reports come from">
        Reports are created from a scan's detail page. Each report is a shareable,
        one-page summary a prospect or client can read without staff present.
    </x-admin.note>

    <x-admin.filters :reset="route('admin.reports.index')" submit-label="Filter">
        <x-admin.search :value="request('search')" placeholder="Business name…" />
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Generated reports</x-slot:title>
        <x-slot:subtitle>{{ $reports->total() }} {{ Str::plural('report', $reports->total()) }} matching</x-slot:subtitle>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Website</th>
                        <th>Score</th>
                        <th>Band</th>
                        <th>
                            <x-admin.sort column="generated_at" label="Generated"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" default-direction="desc" />
                        </th>
                        <th>
                            <x-admin.sort column="downloads" label="Downloads"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" default-direction="desc" />
                        </th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($reports as $report)
                        <tr>
                            <td class="font-semibold text-gray-900">{{ $report->scan->website->business_name ?? '—' }}</td>
                            <td>
                                <span class="admin-muted max-w-[14rem] inline-block truncate align-bottom">
                                    {{ $report->scan->url ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <x-admin.badge :variant="$scoreVariant($report->scan->score ?? null)">
                                    {{ $report->scan->score ?? '—' }}/100
                                </x-admin.badge>
                            </td>
                            <td class="text-gray-600">{{ $report->scan->band ?? '—' }}</td>
                            <td>
                                <span class="text-gray-700">{{ $report->generated_at?->diffForHumans() ?? '—' }}</span>
                                @if ($report->generated_at)
                                    <span class="block text-xs text-gray-400">{{ $report->generated_at->format('j M Y') }}</span>
                                @endif
                            </td>
                            <td class="admin-tabular text-gray-700">{{ $report->downloads ?? 0 }}</td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.reports.download', $report)" icon="download" title="Download PDF">Download</x-admin.action>
                                    @if ($report->scan)
                                        <x-admin.action :href="route('admin.scans.show', $report->scan)" icon="eye" title="Open the source scan" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="7" icon="document-text" title="No reports yet"
                                       text="Open a completed scan and generate a report — it will appear here with its download count." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$reports" label="reports" />
    </x-admin.card>
</div>

@endsection