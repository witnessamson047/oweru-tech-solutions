@extends('layouts.admin')

@section('title', 'Enquiries')
@section('page-title', 'Enquiries')
@section('page-subtitle', 'Every enquiry that has come in, from any channel')

@section('content')

@php
    $stageVariant = [
        'new' => 'info',
        'qualified' => 'gold',
        'diagnostic_paid' => 'warning',
        'proposal_sent' => 'warning',
        'won' => 'success',
        'lost' => 'neutral',
    ];
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-admin.stat label="All enquiries" :value="$stats['total']" icon="inbox" />
        <x-admin.stat label="Needs triage" :value="$stats['new']" icon="bell"
                      :href="route('admin.enquiries.index', ['stage' => 'new'])" />
        <x-admin.stat label="Won" :value="$stats['won']" icon="check"
                      :href="route('admin.enquiries.index', ['stage' => 'won'])" />
        <x-admin.stat label="This month" :value="$stats['this_month']" icon="calendar" />
    </div>

    @if ($stats['new'] > 0)
        <x-admin.note tone="warning" :title="$stats['new'].' '.\Illuminate\Support\Str::plural('enquiry', $stats['new']).' waiting to be triaged'">
            New enquiries have not been qualified yet. Open each one, decide if it is a real
            opportunity, and move it to <strong>Qualified</strong> or <strong>Lost</strong>.
            Everything with a stage is tracked on the <a class="admin-inline-link" href="{{ route('admin.pipeline.index') }}">pipeline</a>.
        </x-admin.note>
    @endif

    <x-admin.filters :reset="route('admin.enquiries.index')">
        <x-admin.search :value="request('search')" placeholder="Name, email, business, phone…" />

        <div class="field">
            <label for="filter-stage">Stage</label>
            <select name="stage" id="filter-stage">
                <option value="">All stages</option>
                @foreach ($stages as $stage)
                    <option value="{{ $stage }}" @selected(request('stage') === $stage)>
                        {{ ucwords(str_replace('_', ' ', $stage)) }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-source">Source</label>
            <select name="source" id="filter-source">
                <option value="">Any source</option>
                @foreach (['website', 'scanner', 'referral', 'manual'] as $source)
                    <option value="{{ $source }}" @selected(request('source') === $source)>{{ ucfirst($source) }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-owner">Owner</label>
            <select name="owner" id="filter-owner">
                <option value="">Anyone</option>
                @foreach ($owners as $owner)
                    <option value="{{ $owner->id }}" @selected((string) request('owner') === (string) $owner->id)>
                        {{ $owner->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Enquiries</x-slot:title>
        <x-slot:subtitle>{{ $enquiries->total() }} {{ \Illuminate\Support\Str::plural('result', $enquiries->total()) }}</x-slot:subtitle>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort column="name" label="Contact"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="business_name" label="Business"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Package</th>
                        <th>
                            <x-admin.sort column="stage" label="Stage"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Budget</th>
                        <th>Source</th>
                        <th>
                            <x-admin.sort column="created_at" label="Received"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']"
                                          default-direction="desc" />
                        </th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enquiries as $enquiry)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $enquiry->name }}</div>
                                <div class="text-xs text-gray-500">{{ $enquiry->email }}</div>
                            </td>
                            <td class="text-gray-700">{{ $enquiry->business_name ?: '—' }}</td>
                            <td class="text-gray-600">
                                {{ $enquiry->package_name ?: ($enquiry->package?->name ?? '—') }}
                            </td>
                            <td>
                                <x-admin.badge :variant="$stageVariant[$enquiry->stage] ?? 'neutral'">
                                    {{ ucwords(str_replace('_', ' ', $enquiry->stage)) }}
                                </x-admin.badge>
                            </td>
                            <td class="text-gray-600">
                                {{ $enquiry->budget_range ? ucwords(str_replace('_', ' ', $enquiry->budget_range)) : '—' }}
                            </td>
                            <td>
                                <span class="admin-badge admin-badge-neutral !text-[11px]">
                                    {{ ucfirst($enquiry->source ?? 'direct') }}
                                </span>
                            </td>
                            <td class="text-gray-500 whitespace-nowrap" title="{{ $enquiry->created_at }}">
                                {{ $enquiry->created_at->diffForHumans(short: true) }}
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.enquiries.show', $enquiry)" icon="eye" title="Open">Open</x-admin.action>
                                    @if ($enquiry->scan)
                                        <x-admin.action :href="route('admin.scans.show', $enquiry->scan)" icon="scan" title="View scan" />
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="8" icon="inbox" title="No enquiries match these filters"
                                       text="Try clearing the filters, or check back after the next scan or form submission.">
                            <a href="{{ route('admin.enquiries.index') }}" class="admin-btn-ghost">Clear filters</a>
                        </x-admin.empty>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$enquiries" label="enquiries" />
    </x-admin.card>
</div>

@endsection