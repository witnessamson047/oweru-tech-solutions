@extends('layouts.admin')

@section('title', 'Scan Results')
@section('page-title', 'Scan Results')
@section('page-subtitle', $scan->website->business_name ?? $scan->url)

@section('content')

@php
    use App\Services\PageSpeedService;

    $score = $scan->score ?? 0;
    $change = $scan->scoreChange();
    $scoreClass = $score === null ? 'admin-score-neutral'
        : ($score < 40 ? 'admin-score-critical' : ($score < 60 ? 'admin-score-weak' : ($score < 80 ? 'admin-score-fair' : 'admin-score-strong')));
    $bandVariant = $score >= 80 ? 'success' : ($score >= 60 ? 'info' : ($score >= 40 ? 'warning' : 'danger'));
    $areas = [
        'Security' => 'key', 'Mobile' => 'globe', 'Speed' => 'refresh', 'Function' => 'checklist',
        'Findability' => 'search', 'Trust' => 'care-plan', 'Commerce' => 'money', 'Freshness' => 'clock',
    ];
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    <div class="lg:col-span-2 space-y-5">

        <x-admin.card>
            <div class="flex flex-col items-center gap-6 sm:flex-row">
                <span class="admin-score admin-score-lg {{ $scoreClass }} admin-tabular shrink-0">{{ $score }}</span>

                <div class="min-w-0 flex-1 text-center sm:text-left">
                    <h2 class="text-xl font-bold text-gray-900">{{ $scan->website->business_name ?? 'Unknown site' }}</h2>
                    <p class="admin-muted break-all text-sm">{{ $scan->url }}</p>
                    <div class="mt-3 flex flex-wrap items-center justify-center gap-3 sm:justify-start">
                        <x-admin.badge :variant="$bandVariant">{{ $scan->band }} ({{ $score }}/100)</x-admin.badge>
                        @if ($change !== null)
                            <span class="inline-flex items-center gap-1 text-sm font-bold"
                                  style="color: {{ $change > 0 ? 'var(--admin-success)' : ($change < 0 ? 'var(--admin-danger)' : 'var(--admin-muted)') }}">
                                <x-admin.icon :name="$change >= 0 ? 'trend-up' : 'trend-down'" class="w-4 h-4" />
                                {{ $change > 0 ? '+'.$change : $change }} pts
                                <span class="font-normal text-xs text-gray-400">vs previous scan</span>
                            </span>
                        @endif
                        <span class="text-xs text-gray-400">{{ $scan->started_at?->format('d M Y H:i') ?? $scan->created_at->format('d M Y H:i') }}</span>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap justify-center gap-2 sm:ml-auto">
                    {{-- Report generation writes a file and sends an email, so it is a POST. --}}
                    <form method="POST" action="{{ route('admin.reports.generate', $scan) }}">
                        @csrf
                        <button type="submit" class="admin-btn">
                            <x-admin.icon name="document-text" class="w-4 h-4" /> Generate report
                        </button>
                    </form>
                    <button type="button" onclick="scanWebsite({{ $scan->website_id }})" class="admin-btn-ghost">
                        <x-admin.icon name="refresh" class="w-4 h-4" /> Re-scan
                    </button>
                </div>
            </div>
        </x-admin.card>

        @if ($scan->pagespeed)
            @php
                $psi = $scan->pagespeed;
                $psiScore = $psi['performance_score'] ?? null;
                $psiBand = PageSpeedService::band(is_numeric($psiScore) ? (int) $psiScore : null);
                $psiVariant = $psiBand === 'Good' ? 'success' : ($psiBand === 'Poor' ? 'danger' : 'warning');
            @endphp
            <x-admin.card title="Real-world mobile performance" icon="pulse"
                          subtitle="Google PageSpeed Insights · {{ \Carbon\Carbon::parse($psi['measured_at'] ?? now())->format('d M Y H:i') }}">
                <div class="flex items-center gap-6">
                    <div class="text-center">
                        <div class="text-4xl font-extrabold admin-tabular">{{ $psiScore ?? '—' }}</div>
                        <x-admin.badge :variant="$psiVariant" class="mt-1">{{ $psiBand ?? '—' }}</x-admin.badge>
                    </div>
                    <div class="grid flex-1 grid-cols-1 gap-x-8 gap-y-1 text-sm sm:grid-cols-2">
                        @foreach ([
                            'fcp_s' => 'First Contentful Paint',
                            'lcp_s' => 'Largest Contentful Paint',
                            'tbt_s' => 'Total Blocking Time',
                            'cls' => 'Layout Shift (CLS)',
                        ] as $key => $label)
                            <div class="flex justify-between border-b border-gray-50 py-0.5">
                                <span class="text-gray-500">{{ $label }}</span>
                                <span class="font-semibold text-gray-800 admin-tabular">{{ $psi['metrics'][$key] ?? '—' }}{{ isset($psi['metrics'][$key]) && $key !== 'cls' ? 's' : '' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </x-admin.card>
        @endif

        @if ($scan->ai_insight)
            <x-admin.card :padded="true">
                <x-slot:title>What this means</x-slot:title>
                <x-slot:subtitle>Plain-language summary for the pitch</x-slot:subtitle>
                <x-slot:actions>
                    <x-admin.badge :variant="($scan->ai_insight['source'] ?? '') === 'ai' ? 'success' : 'info'">
                        {{ ($scan->ai_insight['source'] ?? 'fallback') === 'ai' ? 'AI-generated' : 'Auto-summary' }}
                    </x-admin.badge>
                </x-slot:actions>

                <p class="text-sm leading-relaxed text-gray-700">{{ $scan->ai_insight['summary'] ?? '' }}</p>

                @if (! empty($scan->ai_insight['next_actions']))
                    <div class="mt-4">
                        <p class="admin-label mb-1">Next actions</p>
                        <ul class="list-inside list-disc space-y-0.5 text-sm text-gray-700">
                            @foreach ($scan->ai_insight['next_actions'] as $action)
                                <li>{{ $action }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (! empty($scan->ai_insight['pitch_email']))
                    <details class="mt-3">
                        <summary class="cursor-pointer text-xs font-semibold" style="color: var(--gold-dark)">View pitch email draft</summary>
                        <pre class="mt-2 whitespace-pre-wrap rounded bg-gray-50 p-3 text-xs text-gray-700">{{ $scan->ai_insight['pitch_email'] }}</pre>
                    </details>
                @endif
            </x-admin.card>
        @endif

        <x-admin.card :padded="false">
            <x-slot:title>Check results</x-slot:title>
            <x-slot:subtitle>{{ $scan->results->count() }} {{ Str::plural('check', $scan->results->count()) }} performed</x-slot:subtitle>

            <div class="space-y-6 p-5">
                @forelse ($scan->results->groupBy('area') as $area => $results)
                    @php
                        $areaPassed = $results->where('passed', true)->count();
                        $areaTotal = $results->count();
                        $areaPoints = $results->where('passed', true)->sum('points');
                        $areaMaxPoints = $results->sum('points');
                        $pct = $areaMaxPoints > 0 ? ($areaPoints / $areaMaxPoints * 100) : 0;
                    @endphp
                    <div>
                        <div class="mb-2 flex items-center justify-between gap-3">
                            <h4 class="flex items-center gap-2 font-semibold text-gray-800">
                                <x-admin.icon :name="$areas[$area] ?? 'checklist'" class="w-4 h-4" />
                                {{ $area }}
                            </h4>
                            <span class="text-sm font-medium admin-tabular {{ $areaPassed === $areaTotal ? 'text-gray-900' : 'text-gray-600' }}">
                                {{ $areaPassed }}/{{ $areaTotal }} passed · {{ $areaPoints }}/{{ $areaMaxPoints }} pts
                            </span>
                        </div>

                        <div class="progress-bar mb-3">
                            <div class="progress-bar-fill {{ $areaPassed === $areaTotal ? 'bg-yellow-500' : 'bg-black' }}"
                                 style="width: {{ $pct }}%"></div>
                        </div>

                        <div class="space-y-2">
                            @foreach ($results as $result)
                                <div class="flex items-start gap-3 rounded-lg p-3 {{ $result->passed ? 'bg-yellow-50' : 'bg-gray-100' }}">
                                    <span class="mt-0.5">
                                        <x-admin.icon :name="$result->passed ? 'check-circle' : 'x-circle'" class="w-4 h-4" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="text-sm font-semibold text-gray-900">{{ $result->check->name ?? $result->check_name }}</p>
                                            <span class="text-xs font-bold {{ $result->passed ? 'text-gray-700' : 'text-gray-500' }}">
                                                {{ $result->passed ? '+'.$result->points.' pts' : '0 pts' }}
                                            </span>
                                        </div>
                                        @if ($result->finding_text)
                                            <p class="mt-1 text-sm text-gray-600">{{ $result->finding_text }}</p>
                                        @endif
                                        @if ($result->evidence)
                                            <p class="mt-1 font-mono text-xs text-gray-400">{{ Str::limit($result->evidence, 150) }}</p>
                                        @endif
                                        @if (! $result->passed && $result->recommendation)
                                            <div class="mt-2 rounded border-l-2 border-yellow-500 bg-yellow-50 p-2">
                                                <p class="text-xs text-gray-700">
                                                    <span class="font-semibold text-yellow-800">Offer:</span>
                                                    {{ $result->recommendation->solution }}
                                                    <x-admin.badge variant="info" class="ml-1">{{ $result->recommendation->service_type }}</x-admin.badge>
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <x-admin.empty icon="scan" title="No check results recorded"
                                   text="This scan has not produced any results yet — it may still be running." />
                @endforelse
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-5">

        <x-admin.card title="Score bands" icon="target">
            <div class="space-y-2 text-sm">
                @foreach ([
                    ['Strong: 80–100', 'var(--gold-light)'],
                    ['Adequate: 60–79', 'var(--gold)'],
                    ['Weak: 40–59', 'var(--gold-dark)'],
                    ['Critical: under 40', 'var(--black)'],
                ] as [$label, $color])
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" style="background-color: {{ $color }}"></span>
                        <span class="text-gray-700">{{ $label }}</span>
                    </div>
                @endforeach
            </div>
        </x-admin.card>

        <x-admin.card title="Top findings" icon="warning">
            <div class="space-y-2">
                @forelse ($scan->results->where('passed', false)->sortBy('points')->take(5) as $finding)
                    <div class="rounded-lg bg-gray-100 p-2.5 text-sm">
                        <p class="text-xs font-semibold text-gray-900">{{ $finding->check->name ?? $finding->check_name }}</p>
                        <p class="mt-0.5 text-xs text-gray-600">{{ Str::limit($finding->finding_text, 80) }}</p>
                    </div>
                @empty
                    <p class="inline-flex items-center gap-1 text-sm text-gray-500">
                        All checks passed <x-admin.icon name="check-circle" class="w-4 h-4" />
                    </p>
                @endforelse
            </div>
        </x-admin.card>

        <x-admin.card title="Scan details" icon="info">
            <dl class="space-y-2 text-xs text-gray-500">
                <div class="flex justify-between">
                    <dt>Status</dt>
                    <dd><x-admin.badge :variant="$scan->status === 'completed' ? 'success' : ($scan->status === 'failed' ? 'danger' : 'warning')">{{ ucfirst($scan->status) }}</x-admin.badge></dd>
                </div>
                <div class="flex justify-between"><dt>Started</dt><dd>{{ $scan->started_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Completed</dt><dd>{{ $scan->completed_at?->format('d M Y H:i') ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt>Source</dt><dd>{{ ucfirst($scan->source ?? 'manual') }}</dd></div>
            </dl>
        </x-admin.card>

        @if ($scan->enquiry)
            <x-admin.card title="Related enquiry" icon="inbox">
                <a href="{{ route('admin.enquiries.show', $scan->enquiry) }}" class="admin-inline-link">
                    {{ $scan->enquiry->name }} — {{ $scan->enquiry->business_name }}
                </a>
            </x-admin.card>
        @endif
    </div>
</div>

@endsection