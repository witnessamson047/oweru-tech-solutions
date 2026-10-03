@extends('layouts.admin')

@section('title', 'Scraped Business — Oweru Admin')
@section('page-title', $business->business_name ?: 'Unnamed business')
@section('page-subtitle', 'Scraped business record')

@section('content')

@php
    $statusVariant = $business->status === 'lead' ? 'success' : ($business->status === 'scanned' ? 'info' : 'neutral');
@endphp

<div class="space-y-5">

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <a href="{{ route('admin.scraped-businesses.index') }}" class="admin-inline-link text-xs">
                <x-admin.icon name="arrow-left" class="mr-1 inline w-3.5 h-3.5" /> Back to scraper
            </a>
            <h1 class="mt-1 text-2xl font-bold text-gray-900">{{ $business->business_name ?: 'Unnamed business' }}</h1>
            <a href="{{ $business->website_url }}" target="_blank" rel="noopener noreferrer" class="admin-inline-link text-sm">
                {{ $business->website_url }}
            </a>
        </div>
        <x-admin.badge :variant="$statusVariant" class="uppercase">{{ $business->status }}</x-admin.badge>
    </div>

    <x-admin.card :padded="false">
        <x-slot:title>Reviews &amp; ratings</x-slot:title>
        <x-slot:subtitle>Extracted from the business's own public pages only (schema.org / on-page testimonials) — no third-party platforms.</x-slot:subtitle>
        <x-slot:actions>
            @if ($business->rating_avg)
                <div class="text-right">
                    <div class="text-2xl font-extrabold admin-tabular" style="color: var(--gold-dark)">
                        {{ number_format($business->rating_avg, 1) }}<span class="text-sm text-gray-400">/5</span>
                    </div>
                    <div class="text-[11px] text-gray-500">
                        {{ $business->rating_count ? $business->rating_count.' '.Str::plural('review', $business->rating_count) : 'on-site rating' }}
                    </div>
                </div>
            @endif
        </x-slot:actions>

        @if ($business->reviews && count($business->reviews))
            <ul class="divide-y divide-gray-50">
                @foreach ($business->reviews as $review)
                    <li class="px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-gray-900">{{ $review['author'] ?: 'Anonymous (site visitor review)' }}</span>
                            <span class="text-[10px] uppercase tracking-wider text-gray-400">via {{ $review['source'] ?? 'their website' }}</span>
                        </div>
                        @if (! empty($review['rating']))
                            <div class="mt-0.5 text-xs" style="color: var(--gold)">
                                {{ str_repeat('★', (int) round($review['rating'])) }}{{ str_repeat('☆', 5 - (int) round($review['rating'])) }} ({{ $review['rating'] }}/5)
                            </div>
                        @endif
                        <p class="mt-1 text-sm leading-relaxed text-gray-600">{{ $review['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-8 text-center text-sm text-gray-400">No reviews or testimonials found on their website.</div>
        @endif
    </x-admin.card>

    <x-admin.card :padded="false">
        <x-slot:title>Extracted public information</x-slot:title>
        <x-slot:subtitle>Scraped {{ $business->created_at->format('d M Y, H:i') }} · source: {{ $business->source_url }}</x-slot:subtitle>

        <dl class="divide-y divide-gray-50">
            @php
                $fields = [
                    ['Business name', $business->business_name ?: '— not found —', false],
                    ['Website', $business->website_url, false],
                    ['Email', $business->email, 'email'],
                    ['Phone', $business->phone, 'phone'],
                    ['Address / location', $business->address ?: '— not found —', false],
                    ['Services', $business->services ?: '— not found —', false],
                    ['About', $business->about ?: '— not found —', false],
                ];
            @endphp
            @foreach ($fields as [$label, $value, $type])
                <div class="grid grid-cols-1 gap-1 px-5 py-4 sm:grid-cols-3">
                    <dt class="admin-label">{{ $label }}</dt>
                    <dd class="text-sm text-gray-900 sm:col-span-2 @if($label === 'About') leading-relaxed @endif">
                        @if ($type === 'email' && $business->email)
                            <a href="mailto:{{ $business->email }}" class="admin-inline-link">{{ $business->email }}</a>
                        @elseif ($type === 'phone' && $business->phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $business->phone) }}" class="admin-inline-link">{{ $business->phone }}</a>
                        @else
                            {{ $value }}
                        @endif
                    </dd>
                </div>
            @endforeach
        </dl>
    </x-admin.card>

    <x-admin.card title="Actions" icon="play" :padded="true">
        <div class="flex flex-wrap items-center gap-3">
            <x-admin.action :action="route('admin.scraped-businesses.run-health-scan', $business)" variant="primary" icon="pulse">
                Run health scan
            </x-admin.action>

            <x-admin.action :action="route('admin.scraped-businesses.rescrape', $business)" icon="refresh"
                            title="Fetch fresh data from their website — refreshes contacts, services, reviews, gaps and feeds the watchdog">
                Re-scrape now
            </x-admin.action>

            @if ($business->enquiry_id)
                <x-admin.action :href="route('admin.enquiries.show', $business->enquiry_id)" icon="inbox">
                    View lead enquiry #{{ $business->enquiry_id }}
                </x-admin.action>
            @else
                <form method="POST" action="{{ route('admin.scraped-businesses.add-to-leads', $business) }}" class="flex flex-wrap items-center gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="Contact name (optional)"
                           class="admin-field w-56 text-sm">
                    <button type="submit" class="admin-btn-ghost">
                        <x-admin.icon name="plus" class="w-4 h-4" /> Add to leads
                    </button>
                </form>
            @endif

            <a href="{{ $business->website_url }}" target="_blank" rel="noopener noreferrer" class="admin-inline-link text-sm">
                Visit website <x-admin.icon name="external" class="inline w-3.5 h-3.5" />
            </a>
        </div>

        @if ($business->website)
            <p class="admin-muted mt-4 text-xs">
                Linked website record #{{ $business->website->id }}
                @if ($business->website->latestScan)
                    · latest health scan: {{ $business->website->latestScan->score }}/100 ({{ $business->website->latestScan->band }})
                    — <a href="{{ route('admin.scans.show', $business->website->latestScan) }}" class="admin-inline-link">view scan</a>
                @endif
            </p>
        @endif
    </x-admin.card>

    @php
        $watchEvents = $business->watchEvents()->latest('id')->limit(10)->get();
    @endphp
    <x-admin.card :padded="false">
        <x-slot:title>Watchdog activity</x-slot:title>
        <x-slot:subtitle>Changes detected between scheduled re-scrapes — hot events (rating drops, negative reviews, outages) alert staff automatically.</x-slot:subtitle>

        @if ($watchEvents->count())
            <ul class="divide-y divide-gray-50">
                @foreach ($watchEvents as $event)
                    <li class="flex items-start justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider {{ $event->style }}">{{ $event->label }}</span>
                                @if ($event->notified_at)
                                    <span class="text-[10px] text-gray-400" title="Staff alerted">Staff alerted {{ $event->notified_at->format('d M H:i') }}</span>
                                @endif
                            </div>
                            @if ($event->summary)
                                <p class="mt-1 text-sm text-gray-700">{{ $event->summary }}</p>
                            @endif
                        </div>
                        <span class="whitespace-nowrap text-[11px] text-gray-400">{{ $event->created_at->format('d M Y, H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <x-admin.empty icon="clock" title="No watchdog events yet"
                           text="Changes appear here after the next scheduled re-scrape." />
        @endif
    </x-admin.card>

    @php
        $priority = $business->outreachPriority();
        $priorityVariant = $priority === 'high' ? 'danger' : ($priority === 'medium' ? 'warning' : 'info');
    @endphp
    <x-admin.card :padded="false">
        <x-slot:title>Preliminary gaps &amp; opportunities</x-slot:title>
        <x-slot:subtitle>Quick triage from the scrape itself — decide who to contact before running a full health scan.</x-slot:subtitle>
        <x-slot:actions>
            <x-admin.badge :variant="$priorityVariant">
                {{ $business->preliminaryGapCount() }} {{ Str::plural('gap', $business->preliminaryGapCount()) }} · {{ $priority }} priority
            </x-admin.badge>
        </x-slot:actions>

        @if ($gapsWithRecs->count())
            <ul class="divide-y divide-gray-50">
                @foreach ($gapsWithRecs as $pair)
                    <li class="px-5 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $pair['gap']['gap'] ?? 'Gap' }}</p>
                                @if ($pair['recommendation'])
                                    @php $rec = $pair['recommendation']; @endphp
                                    <p class="mt-0.5 text-xs font-medium text-green-700">→ Sell: {{ $rec->service_type }} — {{ $rec->solution }}</p>
                                @elseif (! empty($pair['gap']['opportunity']))
                                    <p class="mt-0.5 text-xs font-medium text-green-700">→ Oweru opportunity: {{ $pair['gap']['opportunity'] }}</p>
                                @endif
                            </div>
                            <span class="whitespace-nowrap text-[10px] uppercase tracking-wider text-gray-400">{{ $pair['gap']['check_name'] ?? '' }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-8 text-center text-sm text-gray-400">
                No preliminary gaps detected from the scrape.
                @if (! $business->last_scraped_at || $business->scrape_count === 0)
                    Re-scrape to analyze this site.
                @else
                    A clean quick-pass doesn't mean the site is healthy — the full health scan checks speed, links and server issues too.
                @endif
            </div>
        @endif
    </x-admin.card>

    @php
        $latestScan = $business->website?->latestScan;
        $weaknesses = $latestScan
            ? $latestScan->results->where('passed', false)->sortBy(fn ($r) => [$r->recommendation?->priority === 'high' ? 0 : 1, $r->points])->take(6)->values()
            : $business->weaknesses;
    @endphp
    <x-admin.card :padded="false">
        <x-slot:title>Weaknesses &amp; challenges</x-slot:title>
        <x-slot:subtitle>
            @if ($latestScan)
                From the latest health scan ({{ $latestScan->score }}/100 — {{ $latestScan->band }})
            @else
                Run a health scan to populate weaknesses from the scanner.
            @endif
        </x-slot:subtitle>

        @if ($latestScan?->ai_insight)
            <div class="border-b border-blue-100 bg-blue-50 px-5 py-4">
                <div class="mb-1 flex items-center justify-between gap-2">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-blue-900">Outreach angle</h3>
                    <span class="text-[10px] text-blue-400">{{ ($latestScan->ai_insight['source'] ?? '') === 'ai' ? 'AI-generated' : 'Auto-summary' }}</span>
                </div>
                <p class="text-sm text-blue-900">{{ $latestScan->ai_insight['summary'] ?? '' }}</p>
                @if (! empty($latestScan->ai_insight['next_actions']))
                    <ul class="mt-1 list-inside list-disc space-y-0.5 text-xs text-blue-800">
                        @foreach (array_slice($latestScan->ai_insight['next_actions'], 0, 4) as $action)
                            <li>{{ $action }}</li>
                        @endforeach
                    </ul>
                @endif
                @if (! empty($latestScan->ai_insight['pitch_email']))
                    <details class="mt-2">
                        <summary class="cursor-pointer text-xs font-semibold text-blue-700">Copy-ready pitch email</summary>
                        <pre class="mt-2 whitespace-pre-wrap rounded border border-blue-100 bg-white p-3 text-xs text-gray-700">{{ $latestScan->ai_insight['pitch_email'] }}</pre>
                    </details>
                @endif
            </div>
        @endif

        @if ($weaknesses instanceof \Illuminate\Support\Collection && $weaknesses->count())
            <ul class="divide-y divide-gray-50">
                @foreach ($weaknesses as $w)
                    <li class="px-5 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-gray-900">{{ $w->check_name ?? $w['check_name'] ?? 'Issue' }}</span>
                            <span class="text-[10px] uppercase tracking-wider text-gray-400">{{ $w->area ?? $w['area'] ?? '' }}</span>
                        </div>
                        <p class="mt-0.5 text-sm text-gray-600">{{ $w->finding_text ?? $w['finding_text'] ?? '' }}</p>
                        @if ($w->consequence ?? $w['consequence'] ?? null)
                            <p class="mt-0.5 text-xs italic" style="color: var(--admin-danger)">Impact: {{ $w->consequence ?? $w['consequence'] }}</p>
                        @endif
                        @if ($w->recommendation?->solution ?? null)
                            <p class="mt-1 text-xs font-medium text-green-700">Fix: {{ $w->recommendation->solution }} ({{ $w->recommendation->service_type }})</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @elseif (is_array($weaknesses) && count($weaknesses))
            <ul class="divide-y divide-gray-50">
                @foreach ($weaknesses as $w)
                    <li class="px-5 py-3">
                        <span class="text-sm font-semibold text-gray-900">{{ $w['check_name'] ?? 'Issue' }}</span>
                        <p class="mt-0.5 text-sm text-gray-600">{{ $w['finding_text'] ?? '' }}</p>
                        @if ($w['consequence'] ?? null)
                            <p class="mt-0.5 text-xs italic" style="color: var(--admin-danger)">Impact: {{ $w['consequence'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <x-admin.empty icon="check-circle" title="No weaknesses recorded yet"
                           text="Run a health scan to populate weaknesses from the scanner." />
        @endif
    </x-admin.card>

    <x-admin.card title="Scrape history" icon="refresh">
        <p class="text-sm text-gray-600">
            Scraped {{ $business->scrape_count }} {{ Str::plural('time', $business->scrape_count) }}
            @if ($business->last_scraped_at) · last run {{ $business->last_scraped_at->diffForHumans() }} @endif
            @if ($business->last_scrape_error)
                · <span style="color: var(--admin-danger)">last error: {{ $business->last_scrape_error }}</span>
            @endif
        </p>
    </x-admin.card>
</div>

@endsection