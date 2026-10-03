@extends('layouts.admin')

@section('title', 'Website Discovery')
@section('page-title', 'Website Discovery')
@section('page-subtitle', 'Find business websites by location — no web address needed')

@section('content')

@php
    use App\Models\DiscoveryRun;
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Discovery runs" :value="$stats['total']" icon="search" />
        <x-admin.stat label="Websites queued" :value="$stats['queued']" icon="globe" />
        <x-admin.stat label="Failed runs" :value="$stats['failed']" icon="warning" />
    </div>

    <x-admin.card title="Find business websites" icon="search"
                  subtitle="Pick a place and a business type. Everything found is added to the Auto-Scraper Queue automatically.">
        <form method="POST" action="{{ route('admin.discovery.store') }}"
              onsubmit="var b=this.querySelector('button[type=submit]'); b.disabled=true; b.textContent='Discovering…';"
              class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <x-admin.field name="city" label="Location" required>
                    <input id="city" name="city" type="text" required value="{{ old('city') }}"
                           placeholder="e.g. Dar es Salaam" list="discovery-city-suggestions">
                    <datalist id="discovery-city-suggestions">
                        @foreach (config('owers.discovery.city_suggestions', []) as $suggestion)
                            <option value="{{ $suggestion }}"></option>
                        @endforeach
                    </datalist>
                </x-admin.field>

                <x-admin.field name="category" label="Category">
                    <select id="category" name="category">
                        @foreach (config('owers.discovery.categories') as $key => $label)
                            <option value="{{ $key }}" @selected(old('category', 'all') === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </x-admin.field>

                <x-admin.field name="limit" label="Max records">
                    <input id="limit" name="limit" type="number" min="50" max="{{ config('owers.discovery.max_limit') }}" step="50"
                           value="{{ old('limit', config('owers.discovery.default_limit')) }}">
                </x-admin.field>
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-gray-400 max-w-xl">
                    Tip: “All businesses” finds the most websites in one go. One polite search at a
                    time — the data source is shared public infrastructure. A run can take up to two minutes.
                </p>
                <button type="submit" class="admin-btn-gold">
                    <x-admin.icon name="search" class="w-4 h-4" /> Discover
                </button>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card :padded="false">
        <x-slot:title>Discovery history</x-slot:title>
        <x-slot:subtitle>Every run records what was found and what was queued</x-slot:subtitle>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>When</th>
                        <th>Location / Category</th>
                        <th>Found / Queued</th>
                        <th>By</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($runs as $run)
                        <tr class="align-top">
                            <td class="whitespace-nowrap">
                                <span class="text-gray-700">{{ $run->created_at->diffForHumans() }}</span>
                                <span class="block text-xs text-gray-400">{{ $run->created_at->format('j M Y H:i') }}</span>
                            </td>
                            <td>
                                <div class="font-semibold text-gray-900">{{ $run->city }}</div>
                                <div class="text-xs text-gray-500">{{ $run->category }} · up to {{ $run->limit }} records</div>
                            </td>
                            <td>
                                @if ($run->status === DiscoveryRun::STATUS_COMPLETED)
                                    <span class="font-semibold text-gray-900">{{ $run->stats['unique_websites'] ?? count($run->result['websites'] ?? []) }} websites</span>
                                    <div class="text-xs" style="color: var(--admin-success)">{{ $run->queued_count }} queued</div>
                                    <div class="text-xs text-gray-400">{{ $run->skipped_count }} skipped (already queued / unusable)</div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="text-gray-600">
                                {{ $run->user?->name ?? ($run->source === DiscoveryRun::SOURCE_COMMAND ? 'artisan command' : '—') }}
                            </td>
                            <td>
                                @if ($run->status === DiscoveryRun::STATUS_COMPLETED)
                                    <x-admin.badge variant="success">Completed</x-admin.badge>
                                @elseif ($run->status === DiscoveryRun::STATUS_FAILED)
                                    <x-admin.badge variant="danger">Failed</x-admin.badge>
                                    <div class="text-xs mt-1 max-w-xs" style="color: var(--admin-danger)">{{ Str::limit($run->error, 120) }}</div>
                                @else
                                    <x-admin.badge variant="warning">Running</x-admin.badge>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="5" icon="search" title="No discovery runs yet"
                                       text="Start with the form above — pick a location and category, and the websites found will be queued automatically." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$runs" label="runs" />
    </x-admin.card>
</div>

@endsection