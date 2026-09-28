@extends('layouts.admin')

@section('title', 'Website Discovery — Oweru Admin')
@section('page-title', 'Website Discovery')
@section('page-subtitle', 'Find business websites by location — no web address needed')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Page header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Website Discovery</h1>
        <p class="text-sm text-gray-500 mt-1">Pick a place and a business type, press Discover, and the websites found are queued for automatic scraping — no web address needed. Data source: OpenStreetMap.</p>
    </div>

    {{-- Discover form — the wireframe: Location / Category / Max -> DISCOVER --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-8">
        <h2 class="text-base font-bold text-gray-900 mb-1">Find business websites</h2>
        <p class="text-xs text-gray-500 mb-4">Takes up to two minutes. Everything found is added to the Auto-Scraper Queue automatically.</p>

        <form method="POST" action="{{ route('admin.discovery.store') }}"
              onsubmit="var b=this.querySelector('button[type=submit]'); b.disabled=true; b.textContent='Discovering…';">
            @csrf
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                <div>
                    <label for="city" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Location</label>
                    <input id="city" name="city" type="text" required value="{{ old('city') }}"
                           placeholder="e.g. Dar es Salaam"
                           list="discovery-city-suggestions"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">
                    <datalist id="discovery-city-suggestions">
                        @foreach(config('owers.discovery.city_suggestions', []) as $suggestion)
                            <option value="{{ $suggestion }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div>
                    <label for="category" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Category</label>
                    <select id="category" name="category" class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">
                        @foreach(config('owers.discovery.categories') as $key => $label)
                            <option value="{{ $key }}" {{ old('category', 'all') === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="limit" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Max records</label>
                    <input id="limit" name="limit" type="number" min="50" max="{{ config('owers.discovery.max_limit') }}" step="50"
                           value="{{ old('limit', config('owers.discovery.default_limit')) }}"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-yellow-500 focus:ring-yellow-500">
                </div>
                <button type="submit" class="btn-accent text-sm px-5 py-2.5">🔍 Discover</button>
            </div>
            @error('city')
                <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-400 mt-3">Tip: “All businesses” finds the most websites in one go. One polite search at a time — the data source is shared public infrastructure.</p>
        </form>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Discovery runs</p>
            <p class="text-xl font-extrabold text-gray-900 mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Websites queued (all runs)</p>
            <p class="text-xl font-extrabold text-green-600 mt-1">{{ $stats['queued'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
            <p class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Failed runs</p>
            <p class="text-xl font-extrabold text-red-600 mt-1">{{ $stats['failed'] }}</p>
        </div>
    </div>

    {{-- Run history --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Discovery history</h2>
            <p class="text-xs text-gray-500 mt-0.5">Every run records what was found and what was queued. Queued websites are scraped automatically within minutes.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="admin-table min-w-full text-sm">
                <thead>
                    <tr>
                        <th class="px-6 py-3 text-left">When</th>
                        <th class="px-6 py-3 text-left">Location / Category</th>
                        <th class="px-6 py-3 text-left">Found / Queued</th>
                        <th class="px-6 py-3 text-left">By</th>
                        <th class="px-6 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($runs as $run)
                        <tr class="hover:bg-gray-50 align-top">
                            <td class="px-6 py-4 text-xs text-gray-500 whitespace-nowrap">
                                {{ $run->created_at->diffForHumans() }}
                                <div class="text-[11px] text-gray-400">{{ $run->created_at->format('d M Y H:i') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900">{{ $run->city }}</div>
                                <div class="text-xs text-gray-500">{{ $run->category }} · up to {{ $run->limit }} records</div>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($run->status === App\Models\DiscoveryRun::STATUS_COMPLETED)
                                    <span class="font-semibold text-gray-900">{{ $run->stats['unique_websites'] ?? count($run->result['websites'] ?? []) }} website(s)</span>
                                    <div class="text-green-600 mt-0.5">{{ $run->queued_count }} queued</div>
                                    <div class="text-gray-400">{{ $run->skipped_count }} skipped (already queued / unusable)</div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $run->user?->name ?? ($run->source === App\Models\DiscoveryRun::SOURCE_COMMAND ? 'artisan command' : '—') }}
                            </td>
                            <td class="px-6 py-4">
                                @if($run->status === App\Models\DiscoveryRun::STATUS_COMPLETED)
                                    <span class="badge badge-success text-[10px] uppercase">completed</span>
                                @elseif($run->status === App\Models\DiscoveryRun::STATUS_FAILED)
                                    <span class="badge badge-danger text-[10px] uppercase" title="{{ $run->error }}">failed</span>
                                    <div class="text-[11px] text-red-600 mt-1 max-w-xs">{{ \Illuminate\Support\Str::limit($run->error, 120) }}</div>
                                @else
                                    <span class="badge badge-warning text-[10px] uppercase">running</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-10 text-center text-sm text-gray-400">
                                No discovery runs yet — start with the form above.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-gray-100">
            {{ $runs->links() }}
        </div>
    </div>

</div>
@endsection
