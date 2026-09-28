@extends('layouts.admin')

@section('title', 'Auto-Scraper Queue — Oweru Admin')
@section('page-title', 'Auto-Scraper Queue')
@section('page-subtitle', '24/7 automatic scraping — the scheduler processes due targets every 5 minutes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Scrape Targets</h1>
            <p class="text-sm text-gray-500 mt-1">URLs queued here are scraped automatically, 24/7, and refreshed every {{ config('owers.scraper.refresh_days') }} days to keep data current. Same responsible-scraping rules as the manual scraper.</p>
        </div>
        <form method="POST" action="{{ route('admin.scrape-targets.check-all') }}"
              onsubmit="this.querySelector('button').disabled = true; this.querySelector('button').textContent = 'Queued…';">
            @csrf
            <button type="submit" class="btn-accent text-sm px-5 py-2.5" title="Re-scrape every active target now — refreshes contacts, services, reviews and gaps, and feeds the watchdog">
                🐕 Check All Now
            </button>
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Active</p>
            <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $stats['active'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Paused</p>
            <p class="text-xl font-extrabold text-yellow-600 mt-1">{{ $stats['paused'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Due Now</p>
            <p class="text-xl font-extrabold text-green-600 mt-1">{{ $stats['due'] }}</p>
        </div>
    </div>

    {{-- Bulk add --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">
        <h2 class="text-base font-bold text-gray-900 mb-1">Queue websites for automatic scraping</h2>
        <p class="text-xs text-gray-500 mb-4">Paste URLs — one per line, or separated by spaces/commas. They'll be scraped automatically by the background scheduler.</p>

        <form method="POST" action="{{ route('admin.scrape-targets.store') }}">
            @csrf
            <textarea name="urls" rows="4" required
                placeholder="One address per line — https:// is optional:&#10;abc.co.tz&#10;www.xyzhotel.com, modewjifoundation.org"
                class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">{{ old('urls') }}</textarea>
            <p class="text-xs text-gray-400 mt-1">Just type or paste the addresses — https:// is added automatically. Separate with new lines, spaces or commas.</p>
            <p class="text-xs text-gray-400 mt-1"><strong>Same site?</strong> www.abc.co.tz and abc.co.tz are treated as ONE site (queued once). Different endings like abc.co.tz and abc.com are DIFFERENT websites — both are kept.</p>
            <button type="submit" class="btn-accent text-sm mt-3">＋ Add to Queue</button>
        </form>
    </div>

    {{-- List --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-bold text-gray-900">Queue</h2>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" placeholder="Search URL…" value="{{ request('search') }}" class="rounded-lg border-gray-300 text-sm w-56">
                <select name="status" class="rounded-lg border-gray-300 text-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Paused</option>
                </select>
                <button type="submit" class="btn-outline text-xs px-3 py-2">Filter</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">URL</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-left">Last Scraped</th>
                        <th class="px-6 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($targets as $target)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <a href="{{ $target->url }}" target="_blank" rel="noopener noreferrer" class="text-sm font-medium text-gray-900 hover:text-yellow-600">{{ $target->url }}</a>
                                @if($target->business)
                                    <div class="text-xs text-gray-500 mt-0.5">
                                        → <a href="{{ route('admin.scraped-businesses.show', $target->business) }}" class="text-yellow-600 hover:text-yellow-700">{{ $target->business->business_name ?: 'view record' }}</a>
                                    </div>
                                @endif
                                @if($target->last_error)
                                    <div class="text-xs text-red-600 mt-0.5">Last error: {{ \Illuminate\Support\Str::limit($target->last_error, 90) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge {{ $target->status === App\Models\ScrapeTarget::STATUS_ACTIVE ? 'badge-success' : ($target->status === App\Models\ScrapeTarget::STATUS_PAUSED ? 'badge-warning' : 'badge-gray') }} text-[10px] uppercase">{{ $target->status_label }}</span>
                                <div class="text-[11px] text-gray-400 mt-1">{{ $target->scrape_count }} scrape(s)</div>
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $target->last_scraped_at?->diffForHumans() ?? 'never' }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center justify-end gap-2 flex-wrap">
                                    <form method="POST" action="{{ route('admin.scrape-targets.run-now', $target) }}">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-yellow-600 hover:text-yellow-700">Run Now</button>
                                    </form>
                                    @if($target->status === App\Models\ScrapeTarget::STATUS_ACTIVE)
                                        <form method="POST" action="{{ route('admin.scrape-targets.pause', $target) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-gray-600 hover:text-gray-900">Pause</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('admin.scrape-targets.resume', $target) }}">
                                            @csrf
                                            <button type="submit" class="text-xs font-medium text-green-600 hover:text-green-700">Resume</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('admin.scrape-targets.destroy', $target) }}" onsubmit="return confirm('Remove this target from the auto-scraper queue?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs font-medium text-red-600 hover:text-red-700">Remove</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-400">
                                Queue is empty — paste URLs above and they'll be scraped automatically.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $targets->links() }}
        </div>
    </div>
</div>
@endsection
