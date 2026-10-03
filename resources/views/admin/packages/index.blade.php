@extends('layouts.admin')

@section('title', 'Service Packages')
@section('page-title', 'Service Packages')
@section('page-subtitle', 'The catalogue, pricing and delivery times clients see on the public site')

@section('content')

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Packages" :value="$stats['total']" icon="package" />
        <x-admin.stat label="Live on site" :value="$stats['active']" icon="eye"
                      :href="route('admin.packages.index', ['status' => 'active'])" />
        <x-admin.stat label="Featured" :value="$stats['featured']" icon="sparkles"
                      :href="route('admin.packages.index', ['featured' => 1])" />
    </div>

    <x-admin.note tone="warning" title="Price changes are public immediately">
        A package that is active appears on the public site right away. If you are
        still deciding on pricing, leave it inactive — it stays in this list without
        being advertised.
    </x-admin.note>

    <x-admin.filters :reset="route('admin.packages.index')">
        <x-admin.search :value="request('search')" placeholder="Search packages…" />

        <div class="field">
            <label for="filter-group">Audience</label>
            <select name="group" id="filter-group">
                <option value="">All audiences</option>
                @foreach ($groups as $key => $label)
                    <option value="{{ $key }}" @selected(request('group') === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-line">Service line</label>
            <select name="service_line" id="filter-line">
                <option value="">Any service line</option>
                @foreach ($serviceLines as $line)
                    <option value="{{ $line->id }}" @selected((string) request('service_line') === (string) $line->id)>
                        {{ $line->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-status">Status</label>
            <select name="status" id="filter-status">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active only</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Hidden only</option>
            </select>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Packages</x-slot:title>
        <x-slot:subtitle>{{ $packages->total() }} {{ Str::plural('package', $packages->total()) }} configured</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.packages.create') }}" class="admin-btn">
                <x-admin.icon name="plus" class="w-4 h-4" />
                Add package
            </a>
        </x-slot:actions>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort column="name" label="Package"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Audience</th>
                        <th>Service line</th>
                        <th>
                            <x-admin.sort column="price_tzs" label="TZS"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="price_usd" label="USD"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="delivery_days" label="Delivery"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($packages as $package)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $package->name }}</div>
                                @if ($package->is_featured)
                                    <x-admin.badge variant="gold" class="mt-1 !text-[10px] !px-1.5">Featured</x-admin.badge>
                                @endif
                            </td>
                            <td>
                                <span class="admin-badge admin-badge-neutral !text-[11px]">
                                    {{ $groups[$package->group] ?? $package->group }}
                                </span>
                            </td>
                            <td class="text-gray-600">{{ $package->serviceLine?->name ?? '—' }}</td>
                            <td class="admin-tabular font-medium text-gray-900">{{ number_format($package->price_tzs) }}</td>
                            <td class="admin-tabular font-medium text-gray-900">{{ number_format($package->price_usd) }}</td>
                            <td class="admin-tabular text-gray-700">{{ $package->delivery_days }}d</td>
                            <td>
                                <x-admin.badge :variant="$package->active ? 'success' : 'neutral'">
                                    {{ $package->active ? 'Active' : 'Hidden' }}
                                </x-admin.badge>
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.packages.edit', $package)" icon="pencil" title="Edit">Edit</x-admin.action>
                                    <x-admin.action :action="route('admin.packages.destroy', $package)" method="DELETE"
                                                    variant="danger" icon="trash" title="Delete"
                                                    :confirm="'Delete the package &quot;'.$package->name.'&quot;? This cannot be undone.'" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="8" icon="package" title="No packages found"
                                       text="Packages are what clients buy. Create one to describe a service, its price in both currencies, and how long delivery takes.">
                            <a href="{{ route('admin.packages.create') }}" class="admin-btn">
                                <x-admin.icon name="plus" class="w-4 h-4" /> Create the first package
                            </a>
                        </x-admin.empty>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$packages" label="packages" />
    </x-admin.card>
</div>

@endsection