@extends('layouts.admin')

@section('title', 'Care Plans')
@section('page-title', 'Monthly Care Plans')
@section('page-subtitle', 'Recurring maintenance and support plans you sell after delivery')

@section('content')

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-admin.stat label="Care plans" :value="$stats['total']" icon="care-plan" />
        <x-admin.stat label="Active on site" :value="$stats['active']" icon="eye"
                      :href="route('admin.care-plans.index', ['status' => 'active'])" />
        <x-admin.stat label="Featured" :value="$stats['featured']" icon="sparkles"
                      :href="route('admin.care-plans.index', ['featured' => 1])" />
        <x-admin.stat label="Recurring per month" icon="trend-up" featured
                      :hint="'TZS '.number_format($stats['monthly_tzs']).'  ·  $'.number_format($stats['monthly_usd'])"
                      value="{{ number_format($stats['monthly_tzs']) }}" suffix="TZS" />
    </div>

    <x-admin.note tone="info" title="These plans are your repeat revenue">
        A care plan keeps a client on a monthly retainer after their site is built.
        Active plans appear on the public pricing page; inactive ones are hidden but
        kept for your records.
    </x-admin.note>

    <x-admin.filters :reset="route('admin.care-plans.index')">
        <x-admin.search :value="request('search')" placeholder="Search plans…" />

        <div class="field">
            <label for="filter-status">Status</label>
            <select name="status" id="filter-status">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active only</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
            </select>
        </div>

        <div class="field">
            <label class="admin-checkbox !text-[11px] uppercase tracking-wider font-bold">
                <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured'))>
                Featured only
            </label>
        </div>
    </x-admin.filters>

    <x-admin.card :padded="false">
        <x-slot:title>Care plans</x-slot:title>
        <x-slot:subtitle>{{ $carePlans->total() }} {{ Str::plural('plan', $carePlans->total()) }} configured</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.care-plans.create') }}" class="admin-btn">
                <x-admin.icon name="plus" class="w-4 h-4" />
                Add care plan
            </a>
        </x-slot:actions>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort column="name" label="Plan"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Description</th>
                        <th>
                            <x-admin.sort column="price_tzs" label="TZS / month"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="price_usd" label="USD / month"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>Status</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($carePlans as $plan)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $plan->name }}</div>
                                @if ($plan->is_featured)
                                    <x-admin.badge variant="gold" class="mt-1 !text-[10px] !px-1.5">
                                        Featured
                                    </x-admin.badge>
                                @endif
                            </td>
                            <td class="max-w-[22rem] text-gray-600">{{ Str::limit($plan->description ?: '—', 110) }}</td>
                            <td class="admin-tabular font-medium text-gray-900">TZS {{ number_format($plan->price_tzs) }}</td>
                            <td class="admin-tabular font-medium text-gray-900">${{ number_format($plan->price_usd) }}</td>
                            <td>
                                <x-admin.badge :variant="$plan->active ? 'success' : 'neutral'">
                                    {{ $plan->active ? 'Active' : 'Hidden' }}
                                </x-admin.badge>
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.care-plans.edit', $plan)" icon="pencil"
                                                    title="Edit">Edit</x-admin.action>
                                    <x-admin.action :action="route('admin.care-plans.destroy', $plan)"
                                                    method="DELETE" variant="danger" icon="trash"
                                                    :confirm="'Delete the care plan &quot;'.$plan->name.'&quot;? This cannot be undone.'"
                                                    title="Delete" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="6" icon="care-plan"
                                       title="No care plans yet"
                                       text="Care plans are monthly retainers for hosting, maintenance and support — the revenue that keeps clients on after delivery.">
                            <a href="{{ route('admin.care-plans.create') }}" class="admin-btn">
                                <x-admin.icon name="plus" class="w-4 h-4" /> Create the first plan
                            </a>
                        </x-admin.empty>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$carePlans" label="care plans" />
    </x-admin.card>
</div>

@endsection