@props([
    'tone' => 'default',
    'title' => null,
    'icon' => null,
])

{{--
    A short explanation strip. Used for "how this works" explainers, engine-offline
    warnings and destructive-action confirmations.

    @props string $tone default | info | warn | danger
--}}

@php
    $tones = ['default' => '', 'info' => 'is-info', 'warn' => 'is-warn', 'danger' => 'is-danger'];
    $icons = ['default' => 'info', 'info' => 'info', 'warn' => 'warning', 'danger' => 'warning'];
    $toneClass = $tones[$tone] ?? '';
    $iconName = $icon ?? $icons[$tone] ?? 'info';
@endphp

<div {{ $attributes->class(['admin-note', $toneClass]) }}>
    <x-admin.icon :name="$iconName" />
    <div class="min-w-0">
        @if ($title)<p class="admin-note-title">{{ $title }}</p>@endif
        <div class="{{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>
    </div>
</div>