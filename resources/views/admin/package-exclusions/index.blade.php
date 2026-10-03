@extends('layouts.admin')

@section('title', 'Package Exclusions')
@section('page-title', 'Package Exclusions')
@section('page-subtitle', 'What is NOT included in a standard package — shown on the public pricing page')

@section('content')

<div class="space-y-5">

    <x-admin.note tone="info" title="Why this matters">
        These lines appear under the public packages page. They set expectations before a
        client signs, which is what stops the "you said the domain was included" argument
        later. Keep them specific and honest.
    </x-admin.note>

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Total exclusions" :value="$stats['total']" icon="shield-off" />
        <x-admin.stat label="Active on public page" :value="$stats['active']" icon="eye"
                      :href="route('admin.package-exclusions.index', ['status' => 'active'])" />
        <x-admin.stat label="Hidden" :value="$stats['total'] - $stats['active']" icon="ban" />
    </div>

    <x-admin.filters :reset="route('admin.package-exclusions.index')">
        <x-admin.search :value="request('search')" placeholder="Search exclusions…" />

        <div class="field">
            <label for="filter-status">Status</label>
            <select name="status" id="filter-status">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active only</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
            </select>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Exclusion list</x-slot:title>
        <x-slot:subtitle>Order controls where each line appears on the public page</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.package-exclusions.create') }}" class="admin-btn">
                <x-admin.icon name="plus" class="w-4 h-4" />
                Add exclusion
            </a>
        </x-slot:actions>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-14">
                            <x-admin.sort column="sort_order" label="#"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="description" label="Description"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Details</th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exclusions as $exclusion)
                        <tr>
                            <td class="admin-tabular text-gray-400">{{ $exclusion->sort_order }}</td>
                            <td class="font-semibold text-gray-900">{{ $exclusion->description }}</td>
                            <td class="max-w-[22rem] text-gray-600">
                                {{ Str::limit($exclusion->details ?: '—', 110) }}
                            </td>
                            <td>
                                <x-admin.badge :variant="$exclusion->active ? 'success' : 'neutral'">
                                    {{ $exclusion->active ? 'Active' : 'Hidden' }}
                                </x-admin.badge>
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.package-exclusions.edit', $exclusion)" icon="pencil"
                                                    title="Edit">Edit</x-admin.action>
                                    <x-admin.action :action="route('admin.package-exclusions.destroy', $exclusion)"
                                                    method="DELETE" variant="danger" icon="trash"
                                                    :confirm="'Delete the exclusion &quot;'.$exclusion->description.'&quot;?'"
                                                    title="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="5" icon="shield-off"
                                       title="No exclusions configured"
                                       text="Without this list the public packages page implies everything is included. Add the things that are billed separately.">
                            <a href="{{ route('admin.package-exclusions.create') }}" class="admin-btn">
                                <x-admin.icon name="plus" class="w-4 h-4" /> Add the first exclusion
                            </a>
                        </x-admin.empty>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$exclusions" label="exclusions" />
    </x-admin.card>
</div>

@endsection