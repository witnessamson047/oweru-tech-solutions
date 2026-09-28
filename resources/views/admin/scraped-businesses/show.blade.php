@extends('layouts.admin')

@section('title', 'Scraped Business — Oweru Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('admin.scraped-businesses.index') }}" class="text-xs font-medium text-gray-500 hover:text-gray-700">← Back to Scraper</a>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $business->business_name ?: 'Unnamed business' }}</h1>
            <a href="{{ $business->website_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-yellow-600 hover:text-yellow-700">{{ $business->website_url }}</a>
        </div>
        <span class="badge {{ $business->status === 'lead' ? 'badge-success' : ($business->status === 'scanned' ? 'badge-info' : 'badge-gray') }} text-xs uppercase">{{ $business->status }}</span>
    </div>

    {{-- Public reputation (from their own site only) --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Reviews & Ratings</h2>
                <p class="text-xs text-gray-500 mt-0.5">Extracted from the business's own public pages only (schema.org / on-page testimonials) — no third-party platforms.</p>
            </div>
            @if($business->rating_avg)
                <div class="text-right">
                    <div class="text-2xl font-extrabold text-yellow-600">{{ number_format($business->rating_avg, 1) }}<span class="text-sm text-gray-400">/5</span></div>
                    <div class="text-[11px] text-gray-500">{{ $business->rating_count ? $business->rating_count . ' review' . ($business->rating_count == 1 ? '' : 's') : 'on-site rating' }}</div>
                </div>
            @endif
        </div>
        @if($business->reviews && count($business->reviews))
            <ul class="divide-y divide-gray-50">
                @foreach($business->reviews as $review)
                    <li class="px-6 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-sm font-semibold text-gray-900">{{ $review['author'] ?: 'Anonymous (site visitor review)' }}</span>
                            <span class="text-[10px] uppercase tracking-wider text-gray-400">via {{ $review['source'] ?? 'their website' }}</span>
                        </div>
                        @if(!empty($review['rating']))
                            <div class="text-yellow-500 text-xs mt-0.5">{{ str_repeat('★', (int) round($review['rating'])) }}{{ str_repeat('☆', 5 - (int) round($review['rating'])) }} ({{ $review['rating'] }}/5)</div>
                        @endif
                        <p class="text-sm text-gray-600 mt-1 leading-relaxed">{{ $review['text'] }}</p>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-8 text-center text-sm text-gray-400">No reviews or testimonials found on their website.</div>
        @endif
    </div>

    {{-- Extracted information --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Extracted Public Information</h2>
            <p class="text-xs text-gray-500 mt-0.5">Scraped {{ $business->created_at->format('d M Y, H:i') }} · source: {{ $business->source_url }}</p>
        </div>
        <dl class="divide-y divide-gray-50">
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Business Name</dt>
                <dd class="sm:col-span-2 text-sm text-gray-900">{{ $business->business_name ?: '— not found —' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Website</dt>
                <dd class="sm:col-span-2 text-sm text-gray-900">{{ $business->website_url }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</dt>
                <dd class="sm:col-span-2 text-sm text-gray-900">
                    @if($business->email)
                        <a href="mailto:{{ $business->email }}" class="text-yellow-600 hover:text-yellow-700">{{ $business->email }}</a>
                    @else
                        — not found —
                    @endif
                </dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Phone</dt>
                <dd class="sm:col-span-2 text-sm text-gray-900">
                    @if($business->phone)
                        <a href="tel:{{ preg_replace('/[^\d+]/', '', $business->phone) }}" class="text-yellow-600 hover:text-yellow-700">{{ $business->phone }}</a>
                    @else
                        — not found —
                    @endif
                </dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Address / Location</dt>
                <dd class="sm:col-span-2 text-sm text-gray-900">{{ $business->address ?: '— not found —' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Services</dt>
                <dd class="sm:col-span-2 text-sm text-gray-900">{{ $business->services ?: '— not found —' }}</dd>
            </div>
            <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-1">
                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">About</dt>
                <dd class="sm:col-span-2 text-sm text-gray-900 leading-relaxed">{{ $business->about ?: '— not found —' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Actions --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="text-base font-bold text-gray-900 mb-4">Actions</h2>
        <div class="flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('admin.scraped-businesses.run-health-scan', $business) }}">
                @csrf
                <button type="submit" class="btn-accent text-sm">⚡ Run Health Scan</button>
            </form>

            <form method="POST" action="{{ route('admin.scraped-businesses.rescrape', $business) }}">
                @csrf
                <button type="submit" class="btn-outline text-sm" title="Fetch fresh data from their website — refreshes contacts, services, reviews, gaps and feeds the watchdog">⟳ Re-scrape Now</button>
            </form>

            @if($business->enquiry_id)
                <a href="{{ route('admin.enquiries.show', $business->enquiry_id) }}" class="btn-outline text-sm">View Lead Enquiry #{{ $business->enquiry_id }}</a>
            @else
                <form method="POST" action="{{ route('admin.scraped-businesses.add-to-leads', $business) }}" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="name" placeholder="Contact name (optional)"
                           class="rounded-lg border-gray-300 text-sm w-56">
                    <button type="submit" class="btn-outline text-sm">＋ Add to Leads</button>
                </form>
            @endif

            <a href="{{ $business->website_url }}" target="_blank" rel="noopener noreferrer" class="text-sm text-gray-500 hover:text-gray-700">Visit website ↗</a>
        </div>

        @if($business->website)
            <p class="text-xs text-gray-500 mt-4">
                Linked website record #{{ $business->website->id }}
                @if($business->website->latestScan)
                    · latest health scan: {{ $business->website->latestScan->score }}/100 ({{ $business->website->latestScan->band }})
                    — <a href="{{ route('admin.scans.show', $business->website->latestScan) }}" class="text-yellow-600 hover:text-yellow-700">view scan</a>
                @endif
            </p>
        @endif
    </div>

    {{-- Watchdog activity (change detection between scheduled re-scrapes) --}}
    @php
        $watchEvents = $business->watchEvents()->latest('id')->limit(10)->get();
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-bold text-gray-900">Watchdog Activity</h2>
                <p class="text-xs text-gray-500 mt-0.5">Changes detected between scheduled re-scrapes — hot events (rating drops, negative reviews, outages) alert staff automatically.</p>
            </div>
        </div>
        @if($watchEvents->count())
            <ul class="divide-y divide-gray-50">
                @foreach($watchEvents as $event)
                    <li class="px-6 py-3 flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full border text-[10px] font-semibold uppercase tracking-wider {{ $event->style }}">{{ $event->label }}</span>
                                @if($event->notified_at)
                                    <span class="text-[10px] text-gray-400" title="Staff alerted">🔔 {{ $event->notified_at->format('d M H:i') }}</span>
                                @endif
                            </div>
                            @if($event->summary)
                                <p class="text-sm text-gray-700 mt-1">{{ $event->summary }}</p>
                            @endif
                        </div>
                        <span class="text-[11px] text-gray-400 whitespace-nowrap">{{ $event->created_at->format('d M Y, H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-8 text-center text-sm text-gray-400">No watchdog events yet — changes appear here after the next scheduled re-scrape.</div>
        @endif
    </div>

    {{-- Preliminary gaps (scrape-time triage, before any health scan) --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-base font-bold text-gray-900">Preliminary Gaps & Opportunities</h2>
                <p class="text-xs text-gray-500 mt-0.5">Quick triage from the scrape itself — decide who to contact before running a full health scan.</p>
            </div>
            <span class="badge {{ $business->outreachPriority() === 'high' ? 'badge-danger' : ($business->outreachPriority() === 'medium' ? 'badge-warning' : 'badge-info') }} text-[10px] uppercase">
                {{ $business->preliminaryGapCount() }} gap{{ $business->preliminaryGapCount() === 1 ? '' : 's' }} • {{ $business->outreachPriority() }} priority
            </span>
        </div>
        @if($gapsWithRecs->count())
            <ul class="divide-y divide-gray-50">
                @foreach($gapsWithRecs as $pair)
                    <li class="px-6 py-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900">{{ $pair['gap']['gap'] ?? 'Gap' }}</p>
                                @if($pair['recommendation'])
                                    @php $rec = $pair['recommendation']; @endphp
                                    <p class="text-xs text-green-700 font-medium mt-0.5">
                                        → Sell: {{ $rec->service_type }} — {{ $rec->solution }}
                                    </p>
                                @elseif(!empty($pair['gap']['opportunity']))
                                    <p class="text-xs text-green-700 font-medium mt-0.5">→ Oweru opportunity: {{ $pair['gap']['opportunity'] }}</p>
                                @endif
                            </div>
                            <span class="text-[10px] uppercase tracking-wider text-gray-400 whitespace-nowrap">{{ $pair['gap']['check_name'] ?? '' }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-8 text-center text-sm text-gray-400">
                No preliminary gaps detected from the scrape.
                @if(!$business->last_scraped_at || $business->scrape_count === 0)
                    Re-scrape to analyze this site.
                @else
                    A clean quick-pass doesn't mean the site is healthy — the full health scan checks speed, links and server issues too.
                @endif
            </div>
        @endif
    </div>

    {{-- Weaknesses & recommendations (from latest health scan) --}}
    @php
        $latestScan = $business->website?->latestScan;
        $weaknesses = $latestScan
            ? $latestScan->results->where('passed', false)->sortBy(fn ($r) => [$r->recommendation?->priority === 'high' ? 0 : 1, $r->points])->take(6)->values()
            : $business->weaknesses;
    @endphp
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="text-base font-bold text-gray-900">Weaknesses & Challenges</h2>
            <p class="text-xs text-gray-500 mt-0.5">
                @if($latestScan)
                    From the latest health scan ({{ $latestScan->score }}/100 — {{ $latestScan->band }})
                @else
                    Run a health scan to populate weaknesses from the scanner.
                @endif
            </p>
        </div>
        @if($latestScan?->ai_insight)
            <div class="px-6 py-4 bg-blue-50 border-b border-blue-100">
                <div class="flex items-center justify-between gap-2 mb-1">
                    <h3 class="text-xs font-bold text-blue-900 uppercase tracking-wider">Outreach angle — what this means</h3>
                    <span class="text-[10px] text-blue-400">{{ ($latestScan->ai_insight['source'] ?? '') === 'ai' ? 'AI-generated' : 'Auto-summary' }}</span>
                </div>
                <p class="text-sm text-blue-900">{{ $latestScan->ai_insight['summary'] ?? '' }}</p>
                @if(!empty($latestScan->ai_insight['next_actions']))
                    <ul class="list-disc list-inside text-xs text-blue-800 mt-1 space-y-0.5">
                        @foreach(array_slice($latestScan->ai_insight['next_actions'], 0, 4) as $action)
                            <li>{{ $action }}</li>
                        @endforeach
                    </ul>
                @endif
                @if(!empty($latestScan->ai_insight['pitch_email']))
                    <details class="mt-2">
                        <summary class="text-xs font-semibold text-blue-700 cursor-pointer">Copy-ready pitch email</summary>
                        <pre class="mt-2 p-3 bg-white rounded border border-blue-100 text-xs text-gray-700 whitespace-pre-wrap">{{ $latestScan->ai_insight['pitch_email'] }}</pre>
                    </details>
                @endif
            </div>
        @endif
        @if($weaknesses instanceof \Illuminate\Support\Collection && $weaknesses->count())
            <ul class="divide-y divide-gray-50">
                @foreach($weaknesses as $w)
                    <li class="px-6 py-3">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm font-semibold text-gray-900">{{ $w->check_name ?? $w['check_name'] ?? 'Issue' }}</span>
                            <span class="text-[10px] uppercase tracking-wider text-gray-400">{{ $w->area ?? $w['area'] ?? '' }}</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $w->finding_text ?? $w['finding_text'] ?? '' }}</p>
                        @if($w->consequence ?? $w['consequence'] ?? null)
                            <p class="text-xs text-red-600 italic mt-0.5">Impact: {{ $w->consequence ?? $w['consequence'] }}</p>
                        @endif
                        @if($w->recommendation?->solution ?? null)
                            <p class="text-xs text-green-700 font-medium mt-1">✓ Fix: {{ $w->recommendation->solution }} ({{ $w->recommendation->service_type }})</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @elseif(is_array($weaknesses) && count($weaknesses))
            <ul class="divide-y divide-gray-50">
                @foreach($weaknesses as $w)
                    <li class="px-6 py-3">
                        <span class="text-sm font-semibold text-gray-900">{{ $w['check_name'] ?? 'Issue' }}</span>
                        <p class="text-sm text-gray-600 mt-0.5">{{ $w['finding_text'] ?? '' }}</p>
                        @if($w['consequence'] ?? null)
                            <p class="text-xs text-red-600 italic mt-0.5">Impact: {{ $w['consequence'] }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-8 text-center text-sm text-gray-400">No weaknesses recorded yet — run a health scan first.</div>
        @endif
    </div>

    {{-- Scrape metadata --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
        <h2 class="text-base font-bold text-gray-900 mb-2">Scrape History</h2>
        <p class="text-xs text-gray-500">
            Scraped {{ $business->scrape_count }} time(s)
            @if($business->last_scraped_at) · last run {{ $business->last_scraped_at->diffForHumans() }} @endif
            @if($business->last_scrape_error) · <span class="text-red-600">last error: {{ $business->last_scrape_error }}</span> @endif
        </p>
    </div>
</div>
@endsection
