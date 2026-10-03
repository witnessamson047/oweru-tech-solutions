@extends('layouts.admin')

@section('title', 'Website Details')
@section('page-title', $website->business_name)
@section('page-subtitle', $website->url)

@section('content')

@php
    $scoreClass = fn ($score) => $score === null ? 'admin-score-neutral'
        : ($score < 40 ? 'admin-score-critical' : ($score < 60 ? 'admin-score-weak' : ($score < 80 ? 'admin-score-fair' : 'admin-score-strong')));
    $bandVariant = fn ($score) => $score === null ? 'neutral'
        : ($score >= 80 ? 'success' : ($score >= 60 ? 'info' : ($score >= 40 ? 'warning' : 'danger')));
@endphp

<div class="grid grid-cols-1 gap-5 lg:grid-cols-3">

    <div class="lg:col-span-2 space-y-5">

        <x-admin.card title="Website information" icon="globe">
            <dl class="grid grid-cols-1 gap-x-6 gap-y-4 sm:grid-cols-2">
                <div>
                    <dt class="admin-label">Business name</dt>
                    <dd class="mt-1 font-semibold text-gray-900">{{ $website->business_name }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Website</dt>
                    <dd class="mt-1">
                        <a href="{{ $website->url }}" target="_blank" rel="noopener noreferrer" class="admin-inline-link break-all">{{ $website->url }}</a>
                    </dd>
                </div>
                <div>
                    <dt class="admin-label">Sector</dt>
                    <dd class="mt-1 font-semibold text-gray-900">{{ $website->sector ?: 'Not specified' }}</dd>
                </div>
                <div>
                    <dt class="admin-label">Status</dt>
                    <dd class="mt-1">
                        <x-admin.badge :variant="$website->exclusion_status === 'excluded' ? 'danger' : 'success'">
                            {{ ucfirst($website->status ?? 'active') }}
                        </x-admin.badge>
                    </dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card title="Scan history" icon="scan"
                      subtitle="{{ $website->scans->count() }} {{ Str::plural('scan', $website->scans->count()) }} recorded">
            <x-slot:actions>
                <button type="button" onclick="scanWebsite({{ $website->id }})" class="admin-btn">
                    <x-admin.icon name="refresh" class="w-4 h-4" /> Run scan
                </button>
            </x-slot:actions>

            <div class="space-y-2.5">
                @forelse ($website->scans as $scan)
                    @php $change = $scan->scoreChange(); @endphp
                    <a href="{{ route('admin.scans.show', $scan) }}"
                       class="flex items-center gap-4 rounded-xl border border-gray-100 p-4 transition hover:border-yellow-200 hover:bg-yellow-50/40">
                        <span class="admin-score {{ $scoreClass($scan->score) }} admin-tabular">{{ $scan->score }}</span>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-admin.badge :variant="$bandVariant($scan->score)">{{ $scan->band }}</x-admin.badge>
                                @if ($change !== null)
                                    <span class="text-xs font-bold" style="color: {{ $change > 0 ? 'var(--admin-success)' : ($change < 0 ? 'var(--admin-danger)' : 'var(--admin-muted)') }}">
                                        {{ $change > 0 ? '+'.$change : $change }}
                                    </span>
                                @endif
                                <span class="text-xs text-gray-500">
                                    {{ $scan->started_at?->format('d M Y H:i') ?? $scan->created_at->format('d M Y H:i') }}
                                </span>
                            </div>
                            <div class="mt-1 text-sm text-gray-600">{{ $scan->results->count() }} checks performed</div>
                        </div>
                        <x-admin.icon name="arrow-right" class="w-4 h-4 text-gray-400" />
                    </a>
                @empty
                    <x-admin.empty icon="scan" title="No scans yet"
                                   text="Run the first scan to see how this website performs across every check." />
                @endforelse
            </div>
        </x-admin.card>
    </div>

    <div class="space-y-5">

        <x-admin.card title="Latest score" icon="pulse">
            @if ($website->latestScan)
                @php $score = $website->latestScan->score; @endphp
                <div class="flex flex-col items-center gap-3 py-2">
                    <span class="admin-score admin-score-lg {{ $scoreClass($score) }} admin-tabular">{{ $score }}</span>
                    <x-admin.badge :variant="$bandVariant($score)">{{ $website->latestScan->band }}</x-admin.badge>
                    <p class="text-xs text-gray-400">
                        Scanned {{ $website->latestScan->created_at->diffForHumans() }}
                    </p>
                </div>
            @else
                <div class="flex flex-col items-center gap-3 py-2">
                    <span class="admin-score admin-score-lg admin-score-neutral admin-tabular">?</span>
                    <p class="text-sm text-gray-400">No scans yet</p>
                </div>
            @endif
        </x-admin.card>

        <x-admin.card title="Linked enquiry" icon="inbox">
            @if ($website->enquiry)
                <a href="{{ route('admin.enquiries.show', $website->enquiry) }}" class="admin-inline-link">
                    {{ $website->enquiry->name }}
                </a>
                <p class="mt-1 text-sm text-gray-500">{{ $website->enquiry->business_name }}</p>
            @else
                <p class="text-sm text-gray-400">No linked enquiry.</p>
            @endif
        </x-admin.card>

        <x-admin.card title="Actions" icon="play">
            <div class="space-y-2">
                <button type="button" onclick="scanWebsite({{ $website->id }})" class="admin-btn w-full justify-center">
                    <x-admin.icon name="refresh" class="w-4 h-4" /> Run internal scan
                </button>
                <a href="{{ $website->url }}" target="_blank" rel="noopener noreferrer" class="admin-btn-ghost w-full justify-center">
                    <x-admin.icon name="external" class="w-4 h-4" /> Visit website
                </a>
                @if ($website->exclusion_status !== 'excluded')
                    <x-admin.action :action="route('admin.websites.exclude', $website)" method="PATCH"
                                    icon="ban" variant="danger" class="w-full justify-center"
                                    :confirm="'Exclude this website from scanning?'">
                        Exclude from scanning
                    </x-admin.action>
                @endif
            </div>
        </x-admin.card>
    </div>
</div>

@endsection