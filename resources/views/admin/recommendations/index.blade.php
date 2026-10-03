@extends('layouts.admin')

@section('title', 'Recommendations')
@section('page-title', 'Recommendation Engine')
@section('page-subtitle', 'Map scanner findings to Oweru services you can sell')

@section('content')

<div class="space-y-5">

    {{-- ---------- Summary ---------- --}}
    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-admin.stat label="Total mappings" :value="$stats['total']" icon="bulb"
                      :href="route('admin.recommendations.index')" />
        <x-admin.stat label="Active" :value="$stats['active']" icon="check-circle"
                      :hint="$stats['total'] > 0 ? round(($stats['active'] / $stats['total']) * 100).'% of the library' : 'Nothing configured yet'"
                      :href="route('admin.recommendations.index', ['status' => 'active'])" />
        <x-admin.stat label="Service categories" :value="$stats['categories']" icon="target"
                      :href="route('admin.recommendations.index')" />
        <x-admin.stat label="Coverage" :value="$stats['active'] > 0 ? 'Live' : 'Setup'" icon="sparkles" featured
                      :hint="$stats['active'] > 0 ? 'Scans attach pitches automatically' : 'Add a mapping to get started'" />
    </div>

    {{-- ---------- How it works ---------- --}}
    <x-admin.note tone="info" title="How the recommendation engine works">
        When a health scan fails a check, the engine looks for a mapping whose
        <span class="font-semibold">check name matches exactly</span> and attaches the solution to the report.
        That is the bridge between diagnosis and sales — the scanner finds the problem,
        this table decides what to offer. Copy check names verbatim from
        <a href="{{ route('admin.scanner-checks.index') }}" class="font-semibold underline">Scanner Checks</a>
        or a mapping will never attach.
    </x-admin.note>

    {{-- ---------- Filters ---------- --}}
    <x-admin.filters :reset="route('admin.recommendations.index')">
        <x-admin.search :value="request('search')" placeholder="Search check, solution or service…" />

        <div class="field">
            <label for="filter-area">Area</label>
            <select name="area" id="filter-area">
                <option value="">All areas</option>
                @foreach ($scannerAreas as $area)
                    <option value="{{ $area }}" @selected(request('area') === $area)>{{ ucfirst($area) }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-service">Service</label>
            <select name="service" id="filter-service">
                <option value="">All services</option>
                @foreach ($serviceTypes as $type)
                    <option value="{{ $type }}" @selected(request('service') === $type)>{{ $type }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-priority">Priority</label>
            <select name="priority" id="filter-priority">
                <option value="">Any priority</option>
                @foreach (['high' => 'High', 'medium' => 'Medium', 'low' => 'Low'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('priority') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label for="filter-status">Status</label>
            <select name="status" id="filter-status">
                <option value="">Any status</option>
                <option value="active" @selected(request('status') === 'active')>Active only</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive only</option>
            </select>
        </div>
    </x-admin.filters>

    {{-- ---------- Table ---------- --}}
    <x-admin.card :padded="false">
        <x-slot:title>Finding → solution mappings</x-slot:title>
        <x-slot:subtitle>{{ $recommendations->total() }} {{ Str::plural('mapping', $recommendations->total()) }} configured</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.recommendations.create') }}" class="admin-btn">
                <x-admin.icon name="plus" class="w-4 h-4" />
                Add mapping
            </a>
        </x-slot:actions>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort column="check_name" label="Failed check"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>What was found</th>
                        <th>Why it matters</th>
                        <th>
                            <x-admin.sort column="service_type" label="Solution"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th>
                            <x-admin.sort column="priority" label="Priority"
                                          :active-sort="$sort['column']" :active-direction="$sort['direction']" />
                        </th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recommendations as $rec)
                        <tr>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $rec->check_name }}</div>
                                <div class="mt-1 flex items-center gap-1.5">
                                    <x-admin.badge variant="neutral" :plain="true" class="!text-[10px] !px-1.5">
                                        {{ ucfirst($rec->area) }}
                                    </x-admin.badge>
                                    @unless ($rec->active)
                                        <x-admin.badge variant="warning" class="!text-[10px] !px-1.5">Inactive</x-admin.badge>
                                    @endunless
                                </div>
                            </td>
                            <td class="max-w-[15rem] text-gray-600">
                                {{ Str::limit($rec->finding_example ?: '—', 70) }}
                            </td>
                            <td class="max-w-[15rem] text-gray-600">
                                {{ Str::limit($rec->consequence ?: '—', 70) }}
                            </td>
                            <td>
                                <div class="font-medium text-gray-900">{{ Str::limit($rec->solution, 60) }}</div>
                                <div class="mt-1">
                                    <x-admin.badge variant="info" :plain="true" class="!text-[10px] !px-1.5">
                                        {{ $rec->service_type }}
                                    </x-admin.badge>
                                </div>
                            </td>
                            <td>
                                <x-admin.badge :variant="['high' => 'danger', 'medium' => 'warning', 'low' => 'neutral'][$rec->priority] ?? 'neutral'">
                                    {{ ucfirst($rec->priority) }}
                                </x-admin.badge>
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.recommendations.edit', $rec)" icon="pencil"
                                                    title="Edit this mapping">Edit</x-admin.action>
                                    <x-admin.action :action="route('admin.recommendations.destroy', $rec)"
                                                    method="DELETE" variant="danger" icon="trash"
                                                    :confirm="'Delete the mapping for '.$rec->check_name.'?'"
                                                    title="Delete this mapping" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="6" icon="bulb"
                                       title="No mappings match these filters"
                                       text="Try clearing the filters, or add a mapping so failed checks turn into sales pitches automatically.">
                            <a href="{{ route('admin.recommendations.create') }}" class="admin-btn">
                                <x-admin.icon name="plus" class="w-4 h-4" /> Add the first mapping
                            </a>
                        </x-admin.empty>
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$recommendations" label="mappings" />
    </x-admin.card>

    {{-- ---------- Common mappings reference ---------- --}}
    <x-admin.card title="Common finding → solution pairings" icon="sparkles"
                  subtitle="Starting points if you are unsure what to map">
        <div class="grid gap-2.5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ([
                ['Slow mobile performance', 'Performance optimisation / Web development'],
                ['Broken links', 'Website maintenance / Development'],
                ['Missing contact path', 'Website redesign / Contact integration'],
                ['Missing privacy or terms', 'Website improvement / Advisory'],
                ['Poor mobile layout', 'Responsive web development'],
                ['No payment or booking path', 'E-commerce / Booking integration'],
            ] as [$problem, $solution])
                <div class="admin-action !justify-start !items-start w-full !p-3 !rounded-lg cursor-default">
                    <x-admin.icon name="warning" class="mt-0.5 shrink-0 text-gold-600" />
                    <span class="min-w-0">
                        <span class="block font-semibold text-gray-900">{{ $problem }}</span>
                        <span class="block text-xs admin-muted">→ {{ $solution }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </x-admin.card>
</div>

@endsection