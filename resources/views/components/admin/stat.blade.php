@props([
    'label',
    'value',
    'hint' => null,
    'icon' => null,
    'href' => null,
    'featured' => false,
    'suffix' => null,
])

{{--
    A single number with context. When given :href it becomes a card that lifts
    on hover and the gold top rule sweeps across — the panel's main "go here"
    affordance.

    <x-admin.stat label="Open enquiries" :value="$count" icon="inbox" :href="route('admin.enquiries.index')" />
--}}

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->class(['admin-card admin-stat block', 'is-featured' => $featured]) }}
>
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="admin-stat-label">{{ $label }}</p>
            <p class="admin-stat-value admin-tabular">
                {{ $value }}@if($suffix)<span class="text-base font-bold ml-0.5">{{ $suffix }}</span>@endif
            </p>
            @if($hint)
                <p class="admin-stat-hint">{{ $hint }}</p>
            @endif
        </div>
        @if($icon)
            <span class="admin-stat-icon shrink-0">
                <x-admin.icon :name="$icon" />
            </span>
        @endif
    </div>
</{{ $tag }}>