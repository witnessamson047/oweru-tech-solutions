@extends('layouts.admin')

@section('title', 'Scraper')
@section('page-title', 'Oweru Scraper')
@section('page-subtitle', 'Extract public business information, then send it to the scanner or the leads pipeline')

@section('content')

<div class="space-y-5">

    <x-admin.note tone="info" title="Responsible, public-only scraping">
        Public pages only — robots.txt is respected, requests are rate-limited to one per two
        seconds, and the scraper identifies itself honestly. Results can be scanned for health
        or turned straight into an enquiry.
    </x-admin.note>

    <x-admin.card title="Scrape a public website" icon="globe"
                  subtitle="Enter a web address — https:// is optional. The page is fetched and its business details extracted.">
        <form method="POST" action="{{ route('admin.scraped-businesses.store') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
            @csrf
            <x-admin.field name="url" label="Website address" required class="flex-1">
                <input type="text" name="url" required inputmode="url" autocomplete="url"
                       placeholder="abc.co.tz" value="{{ old('url') }}">
            </x-admin.field>
            <button type="submit" class="admin-btn-gold whitespace-nowrap">
                <x-admin.icon name="search" class="w-4 h-4" /> Scrape
            </button>
        </form>
    </x-admin.card>

    <x-admin.card :padded="false">
        <x-slot:title>Scraped businesses</x-slot:title>
        <x-slot:subtitle>{{ $businesses->total() }} {{ Str::plural('business', $businesses->total()) }} matching</x-slot:subtitle>
        <x-slot:actions>
            <a href="{{ route('admin.scraped-businesses.export', request()->only(['search', 'status'])) }}" class="admin-btn-ghost"
               title="Download the current list, with your filters, as CSV for outreach planning">
                <x-admin.icon name="download" class="w-4 h-4" /> Export CSV
            </a>
            <a href="{{ route('admin.scrape-targets.index') }}" class="admin-btn-ghost">
                <x-admin.icon name="clock" class="w-4 h-4" /> Auto-Scraper Queue
            </a>
        </x-slot:actions>

        <form method="GET" class="admin-filters" role="search">
            <x-admin.search :value="request('search')" placeholder="Name, URL, email or phone…" />

            <div class="field">
                <label for="filter-status">Status</label>
                <select name="status" id="filter-status">
                    <option value="">Any status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>

            <div class="spacer"></div>
            <div class="flex items-center gap-2">
                <button type="submit" class="admin-btn">
                    <x-admin.icon name="filter" class="w-4 h-4" /> Filter
                </button>
                @if (request()->query())
                    <a href="{{ route('admin.scraped-businesses.index') }}" class="admin-btn-ghost">Reset</a>
                @endif
            </div>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Business</th>
                        <th>Scraped from</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Preliminary gaps</th>
                        <th>Scraped</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($businesses as $business)
                        <tr>
                            <td>
                                <a href="{{ route('admin.scraped-businesses.show', $business) }}" class="admin-inline-link">
                                    {{ $business->business_name ?: 'Unnamed business' }}
                                </a>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $business->website_url }}</div>
                            </td>
                            <td>
                                {{-- The exact URL this record was extracted from — the
                                     canonical form the scraper actually fetched. --}}
                                <a href="{{ $business->source_url ?: $business->website_url }}" target="_blank" rel="noopener noreferrer"
                                   class="admin-muted break-all text-xs"
                                   title="{{ $business->source_url ?: $business->website_url }}">{{ $business->source_url ?: $business->website_url }}</a>
                            </td>
                            <td>
                                <div class="text-gray-700">{{ $business->email ?: '—' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $business->phone ?: '—' }}</div>
                            </td>
                            <td>
                                <x-admin.badge :variant="$business->status === 'lead' ? 'success' : ($business->status === 'scanned' ? 'info' : 'neutral')">
                                    {{ $business->status }}
                                </x-admin.badge>
                            </td>
                            <td>
                                @php $gapCount = $business->preliminaryGapCount(); @endphp
                                @if ($gapCount > 0)
                                    @php
                                        $priority = $business->outreachPriority();
                                        $services = $business->mappedServices($recommendations);
                                    @endphp
                                    <x-admin.badge :variant="$priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'info')"
                                                   :title="collect($business->preliminary_gaps)->pluck('gap')->implode('; ')">
                                        {{ $gapCount }} {{ Str::plural('gap', $gapCount) }}
                                    </x-admin.badge>
                                    {{-- Services to pitch for these gaps, visible without opening the record. --}}
                                    @if ($services->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach ($services->take(2) as $rec)
                                                <span class="text-xs leading-4 px-1.5 py-0.5 rounded border"
                                                      style="background: var(--admin-accent-soft); color: var(--admin-accent); border-color: var(--admin-border)">
                                                    {{ $rec->service_type }}
                                                </span>
                                            @endforeach
                                            @if ($services->count() > 2)
                                                <span class="text-xs text-gray-400" title="{{ $services->pluck('service_type')->implode(', ') }}">+{{ $services->count() - 2 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-gray-600">{{ $business->created_at->format('d M Y') }}</td>
                            <td class="col-actions">
                                <div class="admin-actions">
                                    <x-admin.action :href="route('admin.scraped-businesses.show', $business)" icon="eye" title="View record">View</x-admin.action>
                                    <x-admin.action :action="route('admin.scraped-businesses.run-health-scan', $business)" method="POST"
                                                    icon="scan" title="Run a health scan on this site">Scan</x-admin.action>
                                    @if (! $business->enquiry_id)
                                        <details class="admin-details">
                                            <summary class="admin-action has-icon">
                                                <x-admin.icon name="user" /> Add lead
                                            </summary>
                                            <form method="POST" action="{{ route('admin.scraped-businesses.add-to-leads', $business) }}"
                                                  class="admin-details-panel">
                                                @csrf
                                                <p class="text-xs admin-muted mb-2">Create an enquiry from this business.</p>
                                                <input type="text" name="name" placeholder="Contact name (optional)" class="mb-2">
                                                <button type="submit" class="admin-btn w-full justify-center">
                                                    <x-admin.icon name="plus" class="w-4 h-4" /> Create enquiry
                                                </button>
                                            </form>
                                        </details>
                                    @else
                                        <x-admin.action :href="route('admin.enquiries.show', $business->enquiry_id)" icon="user" title="Open the linked lead">
                                            Lead #{{ $business->enquiry_id }}
                                        </x-admin.action>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <x-admin.empty colspan="7" icon="globe" title="No scraped businesses yet"
                                       text="Enter a URL above to extract public business information. Results appear here, ready to scan or convert into a lead." />
                    @endforelse
                </tbody>
            </table>
        </div>

        <x-admin.pagination :paginator="$businesses" label="businesses" />
    </x-admin.card>
</div>

<script>
    // Close any open "Add lead" popover when clicking elsewhere on the page.
    document.addEventListener('click', function (e) {
        if (! e.target.closest('.admin-details')) {
            document.querySelectorAll('.admin-details[open]').forEach(function (d) { d.open = false; });
        }
    });
</script>

@endsection