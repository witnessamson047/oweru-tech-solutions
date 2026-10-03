@extends('layouts.admin')

@section('title', 'Scanner Checks')
@section('page-title', 'Scanner Checks')
@section('page-subtitle', 'The individual checks, their area and scoring weight')

@section('content')

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Total checks" :value="$stats['total']" icon="checklist" />
        <x-admin.stat label="Enabled" :value="$stats['enabled']" icon="check-circle"
                      :href="route('admin.scanner-checks.index', ['enabled' => 'true'])" />
        <x-admin.stat label="Disabled" :value="$stats['disabled']" icon="pause"
                      :href="route('admin.scanner-checks.index', ['enabled' => 'false'])" />
    </div>

    <x-admin.note tone="warn" title="Changing weights affects every future scan">
        A check's weight is how many points it is worth. Disabling a check removes
        it from scoring entirely, but past scan results are not recalculated.
    </x-admin.note>

    <x-admin.filters :reset="route('admin.scanner-checks.index')" submit-label="Filter">
        <x-admin.search :value="request('search')" placeholder="Name, slug or description…" />

        <div class="field">
            <label for="filter-area">Area</label>
            <select name="area" id="filter-area">
                <option value="">All areas</option>
                @foreach ($areas as $area)
                    <option value="{{ $area }}" @selected(request('area') === $area)>{{ $area }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-enabled">State</label>
            <select name="enabled" id="filter-enabled">
                <option value="">Any state</option>
                <option value="true" @selected(request('enabled') === 'true')>Enabled</option>
                <option value="false" @selected(request('enabled') === 'false')>Disabled</option>
            </select>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Checks</x-slot:title>
        <x-slot:subtitle>{{ $checks->total() }} {{ Str::plural('check', $checks->total()) }} matching</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.scanner-checks.create') }}" class="admin-btn">
                <x-admin.icon name="plus" class="w-4 h-4" /> Add check
            </a>
        </x-slot:actions>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort column="name" label="Check"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="area" label="Area"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="weight" label="Weight"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" default-direction="desc" />
                        </th>
                        <th>State</th>
                        <th>Description</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($checks as $check)
                        <tr class="{{ $check->enabled ? '' : 'opacity-60' }}">
                            <td>
                                <div class="font-semibold text-gray-900">{{ $check->name }}</div>
                                <div class="text-xs text-gray-500">{{ $check->slug }}</div>
                            </td>
                            <td><x-admin.badge variant="info">{{ $check->area }}</x-admin.badge></td>
                            <td>
                                <span class="admin-tabular font-bold text-gray-900">{{ $check->weight }}</span>
                                <span class="text-xs text-gray-500">pts</span>
                            </td>
                            <td>
                                <x-admin.badge :variant="$check->enabled ? 'success' : 'neutral'">
                                    {{ $check->enabled ? 'Enabled' : 'Disabled' }}
                                </x-admin.badge>
                            </td>
                            <td>
                                <span class="admin-muted block max-w-[18rem] truncate" title="{{ $check->description }}">
                                    {{ $check->description ?: '—' }}
                                </span>
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.scanner-checks.edit', $check)" icon="pencil" title="Edit">Edit</x-admin.action>
                                    <x-admin.action :action="route('admin.scanner-checks.destroy', $check)" method="DELETE"
                                                    icon="trash" variant="danger" title="Delete"
                                                    :confirm="'Delete the “'.$check->name.'” check?'" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="6" icon="checklist" title="No scanner checks found"
                                       text="Add a check to define what the scanner looks for, or clear the filters to see them all.">
                            <a href="{{ route('admin.scanner-checks.create') }}" class="admin-btn">
                                <x-admin.icon name="plus" class="w-4 h-4" /> Add the first check
                            </a>
                        </x-admin.empty>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$checks" label="checks" />
    </x-admin.card>
</div>

@endsection