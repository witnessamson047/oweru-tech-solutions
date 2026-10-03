@extends('layouts.admin')

@section('title', 'Pipeline')
@section('page-title', 'Enquiry Pipeline')
@section('page-subtitle', 'Every enquiry moving from first contact to a signed project')

@section('content')

@php
    $stageLabels = [
        'new' => 'New',
        'qualified' => 'Qualified',
        'diagnostic_paid' => 'Diagnostic paid',
        'proposal_sent' => 'Proposal sent',
        'won' => 'Won',
        'lost' => 'Lost',
    ];
    $openStages = ['new', 'qualified', 'diagnostic_paid', 'proposal_sent'];
@endphp

<div class="space-y-5">

    <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
        <x-admin.stat label="Enquiries in pipeline" :value="$total" icon="pipeline" />
        <x-admin.stat label="Open opportunities" :value="$counts->only($openStages)->sum()" icon="clock"
                      :href="route('admin.enquiries.index')" />
        <x-admin.stat label="Revenue collected" icon="money" featured
                      value="{{ number_format($value['won']) }}" suffix="TZS" />
        <x-admin.stat label="Outstanding" icon="warning"
                      value="{{ number_format($value['outstanding']) }}" suffix="TZS" />
    </div>

    <x-admin.note tone="info" title="How the pipeline works">
        A card moves right as the client gets closer to signing: <strong>New</strong> is an
        untriaged enquiry, <strong>Qualified</strong> has real budget, <strong>Diagnostic
        paid</strong> means they have bought the scan, and <strong>Proposal sent</strong> is
        waiting on a decision. Everything still open sits in the first four columns —
        that number is what you should chase.
    </x-admin.note>

    <x-admin.filters :reset="route('admin.pipeline.index')">
        <x-admin.search :value="$search" placeholder="Search business, name, phone…" />

        <div class="field">
            <label for="filter-owner">Owner</label>
            <select name="owner" id="filter-owner">
                <option value="">Everyone</option>
                @foreach ($owners as $member)
                    <option value="{{ $member->id }}" @selected((string) request('owner') === (string) $member->id)>
                        {{ $member->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </x-admin.filters>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6 gap-3">
        @foreach ($stageLabels as $stage => $label)
            @php
                $cards = $pipeline[$stage] ?? collect();
                $age = fn ($d) => $d->diffInDays(now());
                $warm = $cards->filter(fn ($e) => $age($e->created_at) >= 3 && $stage !== 'won' && $stage !== 'lost')->count();
            @endphp

            <section class="kanban-column" aria-labelledby="stage-{{ $stage }}">
                <header class="kanban-column-head">
                    <span class="kanban-column-title" id="stage-{{ $stage }}">{{ $label }}</span>
                    <span class="kanban-column-count">{{ $counts[$stage] ?? 0 }}</span>
                </header>

                <div class="kanban-scroll">
                    @forelse ($cards as $enquiry)
                        @php
                            $days = $age($enquiry->created_at);
                            $ageClass = in_array($stage, ['won', 'lost'], true) ? '' : ($days >= 7 ? 'age-hot' : ($days >= 3 ? 'age-warm' : ''));
                        @endphp

                        <a href="{{ route('admin.enquiries.show', $enquiry) }}"
                           class="kanban-card {{ $ageClass }}">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="font-bold text-[13px] text-gray-900 leading-snug truncate">
                                        {{ $enquiry->business_name ?: $enquiry->name }}
                                    </div>
                                    <div class="text-[11px] text-gray-500 truncate">{{ $enquiry->name }}</div>
                                </div>
                                @if ($enquiry->owner)
                                    <span class="admin-avatar shrink-0" title="{{ $enquiry->owner->name }}">
                                        {{ Str::upper(Str::substr($enquiry->owner->name, 0, 1)) }}
                                    </span>
                                @endif
                            </div>

                            @if ($enquiry->package_name)
                                <div class="mt-2">
                                    <x-admin.badge variant="gold" class="!text-[10px] !px-1.5 !py-0.5">
                                        {{ Str::limit($enquiry->package_name, 20) }}
                                    </x-admin.badge>
                                </div>
                            @endif

                            <div class="mt-2.5 flex items-center justify-between gap-2">
                                <span class="text-[10px] text-gray-400 truncate">
                                    {{ $enquiry->created_at->diffForHumans(short: true) }}
                                </span>
                                @if (! in_array($stage, ['won', 'lost'], true) && $days >= 3)
                                    <span class="kanban-age {{ $days >= 7 ? 'is-hot' : 'is-warm' }}">
                                        {{ $days }}d waiting
                                    </span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <p class="kanban-empty">
                            {{ ($counts[$stage] ?? 0) > 0 ? 'No matching enquiries' : 'Nothing here yet' }}
                        </p>
                    @endforelse

                    @if (($counts[$stage] ?? 0) > $cards->count())
                        <a href="{{ route('admin.enquiries.index', ['stage' => $stage]) }}" class="admin-link text-xs">
                            View all {{ $counts[$stage] }} {{ Str::plural('enquiry', $counts[$stage]) }} →
                        </a>
                    @endif
                </div>
            </section>
        @endforeach
    </div>
</div>

@endsection