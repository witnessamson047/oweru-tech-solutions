@extends('layouts.admin')

@section('title', 'Auto-Scraper Queue')
@section('page-title', 'Auto-Scraper Queue')
@section('page-subtitle', 'URLs scraped automatically, 24/7, and refreshed on a rolling schedule')

@section('content')

@php
    use App\Models\ScrapeTarget;
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-3">
        <x-admin.stat label="Active targets" :value="$stats['active']" icon="globe" />
        <x-admin.stat label="Paused" :value="$stats['paused']" icon="pause" />
        <x-admin.stat label="Due now" :value="$stats['due']" icon="refresh" />
    </div>

    <x-admin.note tone="info" title="Responsible scraping">
        Targets are scraped automatically every 5 minutes and refreshed every
        {{ config('owers.scraper.refresh_days') }} days to keep data current. The same rules
        as the manual scraper apply: robots.txt is respected, requests are rate-limited,
        and the scraper identifies itself honestly.
    </x-admin.note>

    <x-admin.card title="Queue websites for automatic scraping" icon="plus"
                  subtitle="Paste addresses one per line, or separated by spaces or commas — https:// is optional.">
        <form method="POST" action="{{ route('admin.scrape-targets.store') }}" class="space-y-3">
            @csrf
            <x-admin.field name="urls" label="Addresses" required
                           hint="Just type or paste the addresses — https:// is added automatically. Separate with new lines, spaces or commas.">
                <textarea name="urls" rows="4" required
                    placeholder="One address per line — https:// is optional:&#10;abc.co.tz&#10;www.xyzhotel.com, modewjifoundation.org"
                    class="font-mono text-sm">{{ old('urls') }}</textarea>
            </x-admin.field>

            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-gray-400 max-w-xl">
                    <strong>Same site?</strong> www.abc.co.tz and abc.co.tz are treated as one site
                    (queued once). Different endings like abc.co.tz and abc.com are different websites —
                    both are kept.
                </p>
                <button type="submit" class="admin-btn">
                    <x-admin.icon name="plus" class="w-4 h-4" /> Add to queue
                </button>
            </div>
        </form>
    </x-admin.card>

    <x-admin.card :padded="false">
        <x-slot:title>Queue</x-slot:title>
        <x-slot:subtitle>{{ $targets->total() }} {{ Str::plural('target', $targets->total()) }} in the queue</x-slot:subtitle>
        <x-slot:actions>
            <form method="POST" action="{{ route('admin.scrape-targets.check-all') }}">
                @csrf
                <button type="submit" class="admin-btn-gold"
                        title="Re-scrape every active target now — refreshes contacts, services, reviews and gaps">
                    <x-admin.icon name="refresh" class="w-4 h-4" /> Check all now
                </button>
            </form>
        </x-slot:actions>

        <form method="GET" class="admin-filters" role="search">
            <x-admin.search :value="request('search')" placeholder="Search URL…" />

            <div class="field">
                <label for="filter-status">Status</label>
                <select name="status" id="filter-status">
                    <option value="">All targets</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Paused</option>
                </select>
            </div>

            <div class="spacer"></div>
            <div class="flex items-center gap-2">
                <button type="submit" class="admin-btn">
                    <x-admin.icon name="filter" class="w-4 h-4" /> Filter
                </button>
                @if (request()->query())
                    <a href="{{ route('admin.scrape-targets.index') }}" class="admin-btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>URL</th>
                        <th>Status</th>
                        <th>Last scraped</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($targets as $target)
                        <tr>
                            <td>
                                <a href="{{ $target->url }}" target="_blank" rel="noopener noreferrer"
                                   class="admin-inline-link break-all">{{ $target->url }}</a>
                                @if ($target->business)
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        →
                                        <a href="{{ route('admin.scraped-businesses.show', $target->business) }}" class="admin-inline-link">
                                            {{ $target->business->business_name ?: 'view record' }}
                                        </a>
                                    </div>
                                @endif
                                @if ($target->last_error)
                                    <div class="text-xs mt-0.5" style="color: var(--admin-danger)">
                                        Last error: {{ Str::limit($target->last_error, 90) }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <x-admin.badge :variant="$target->status === ScrapeTarget::STATUS_ACTIVE ? 'success' : ($target->status === ScrapeTarget::STATUS_PAUSED ? 'warning' : 'neutral')">
                                    {{ $target->status_label }}
                                </x-admin.badge>
                                <div class="text-xs text-gray-400 mt-1">{{ $target->scrape_count }} {{ Str::plural('scrape', $target->scrape_count) }}</div>
                            </td>
                            <td>
                                <span class="text-gray-700">{{ $target->last_scraped_at?->diffForHumans() ?? 'never' }}</span>
                                @if ($target->last_scraped_at)
                                    <span class="block text-xs text-gray-400">{{ $target->last_scraped_at->format('j M Y H:i') }}</span>
                                @endif
                            </td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :action="route('admin.scrape-targets.run-now', $target)" method="POST"
                                                    icon="refresh" title="Queue a scrape right now">Run</x-admin.action>
                                    @if ($target->status === ScrapeTarget::STATUS_ACTIVE)
                                        <x-admin.action :action="route('admin.scrape-targets.pause', $target)" method="POST"
                                                        icon="pause" title="Pause automatic scraping">Pause</x-admin.action>
                                    @else
                                        <x-admin.action :action="route('admin.scrape-targets.resume', $target)" method="POST"
                                                        icon="play" title="Resume automatic scraping">Resume</x-admin.action>
                                    @endif
                                    <x-admin.action :action="route('admin.scrape-targets.destroy', $target)" method="DELETE"
                                                    icon="trash" variant="danger" title="Remove from the queue"
                                                    :confirm="'Remove this target from the auto-scraper queue?'" />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="4" icon="globe" title="The queue is empty"
                                       text="Paste URLs above and they will be scraped automatically — no web address needed beyond the site itself." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$targets" label="targets" />
    </x-admin.card>
</div>

@endsection