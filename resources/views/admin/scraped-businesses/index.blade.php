@extends('layouts.admin')

@section('title', 'Scraper — Oweru Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Oweru Scraper</h1>
            <p class="text-sm text-gray-500 mt-1">Extract public business information from permitted public websites, then send them straight to the Health Scanner or the leads pipeline.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.scraped-businesses.export', request()->only(['search', 'status'])) }}" class="btn-outline text-sm" title="Download the current list (with your filters) as CSV for outreach planning">⬇ Export CSV</a>
            <a href="{{ route('admin.scrape-targets.index') }}" class="btn-outline text-sm">⏱ Auto-Scraper Queue</a>
        </div>
    </div>

    {{-- Scrape form --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">
        <h2 class="text-base font-bold text-gray-900 mb-1">Scrape a public website</h2>
        <p class="text-xs text-gray-500 mb-4">Public pages only — robots.txt is respected, requests are rate-limited to 1 per 2 seconds, and the scraper identifies itself honestly.</p>

        <form method="POST" action="{{ route('admin.scraped-businesses.store') }}" class="flex flex-col sm:flex-row gap-3">
            @csrf
            <input type="text" name="url" required inputmode="url" autocomplete="url" placeholder="abc.co.tz — https:// is optional" value="{{ old('url') }}"
                   class="flex-1 rounded-lg border-gray-300 focus:border-yellow-500 focus:ring-yellow-500 text-sm">
            <p class="text-xs text-gray-400 sm:hidden">Just type the address — https:// is optional.</p>
            <button type="submit" class="btn-accent text-sm px-6 py-2.5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Scrape
            </button>
        </form>
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-bold text-gray-900">Scraped Businesses</h2>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Search name, URL, email, phone…"
                       value="{{ request('search') }}" class="rounded-lg border-gray-300 text-sm w-56">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-outline text-xs px-3 py-2">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">Business</th>
                        <th class="px-6 py-3 text-left">Scraped From</th>
                        <th class="px-6 py-3 text-left">Contact</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Prelim. Gaps</th>
                        <th class="px-6 py-3 text-left">Scraped</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($businesses as $business)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ route('admin.scraped-businesses.show', $business) }}" class="font-semibold text-gray-900 hover:text-yellow-600">{{ $business->business_name ?: 'Unnamed business' }}</a>
                                <div class="text-xs text-gray-500 mt-0.5">{{ $business->website_url }}</div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                {{-- The exact URL this record was extracted from — the
                                     canonical form the scraper actually fetched. --}}
                                <a href="{{ $business->source_url ?: $business->website_url }}" target="_blank" rel="noopener noreferrer"
                                   class="text-gray-600 hover:text-yellow-700 break-all"
                                   title="{{ $business->source_url ?: $business->website_url }}">{{ $business->source_url ?: $business->website_url }}</a>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-600">
                                <div>{{ $business->email ?: '—' }}</div>
                                <div class="mt-0.5">{{ $business->phone ?: '—' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge {{ $business->status === 'lead' ? 'badge-success' : ($business->status === 'scanned' ? 'badge-info' : 'badge-gray') }} text-[10px] uppercase">{{ $business->status }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php $gapCount = $business->preliminaryGapCount(); @endphp
                                @if($gapCount > 0)
                                    @php
                                        $priority = $business->outreachPriority();
                                        $gapBadge = $priority === 'high' ? 'badge-danger' : ($priority === 'medium' ? 'badge-warning' : 'badge-info');
                                        $services = $business->mappedServices($recommendations);
                                    @endphp
                                    <a href="{{ route('admin.scraped-businesses.show', $business) }}" class="badge {{ $gapBadge }} text-[10px]" title="{{ collect($business->preliminary_gaps)->pluck('gap')->implode('; ') }}">{{ $gapCount }} gap{{ $gapCount === 1 ? '' : 's' }}</a>
                                    {{-- The Oweru services to pitch for these gaps — visible on the
                                         list so staff can prioritize outreach without opening each page. --}}
                                    @if($services->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($services->take(2) as $rec)
                                                <span class="text-[10px] leading-4 px-1.5 py-0.5 rounded bg-green-50 text-green-700 border border-green-100">{{ $rec->service_type }}</span>
                                            @endforeach
                                            @if($services->count() > 2)
                                                <span class="text-[10px] text-gray-400" title="{{ $services->pluck('service_type')->implode(', ') }}">+{{ $services->count() - 2 }}</span>
                                            @endif
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">{{ $business->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    <a href="{{ route('admin.scraped-businesses.show', $business) }}" class="text-xs font-medium text-gray-600 hover:text-gray-900">View</a>
                                    <form method="POST" action="{{ route('admin.scraped-businesses.run-health-scan', $business) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-yellow-600 hover:text-yellow-700">Run Health Scan</button>
                                    </form>
                                    @if(!$business->enquiry_id)
                                        <details class="relative">
                                            <summary class="text-xs font-medium text-gray-600 hover:text-gray-900 cursor-pointer list-none">Add to Leads ▾</summary>
                                            <form method="POST" action="{{ route('admin.scraped-businesses.add-to-leads', $business) }}"
                                                  class="absolute right-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg p-3 z-10">
                                                @csrf
                                                <input type="text" name="name" placeholder="Contact name (optional)"
                                                       class="w-full rounded-lg border-gray-300 text-xs mb-2">
                                                <button type="submit" class="btn-accent w-full justify-center py-2 text-[11px]">Create enquiry</button>
                                            </form>
                                            <script>document.addEventListener('click', e => { if (!e.target.closest('details')) document.querySelectorAll('details[open]').forEach(d => d.open = false); });</script>
                                            <style>details summary::-webkit-details-marker { display: none; }</style>
                                        </details>
                                    @else
                                        <a href="{{ route('admin.enquiries.show', $business->enquiry_id) }}" class="text-xs font-medium text-green-600 hover:text-green-700">Lead #{{ $business->enquiry_id }}</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-400">
                                No scraped businesses yet — enter a URL above to extract public business information.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $businesses->links() }}
        </div>
    </div>
</div>
@endsection
