@props([
    'variant' => 'neutral',
    'plain' => false,
    'dot' => true,
])

{{--
    Status pill with a leading dot, so a coloured pill still reads as "state"
    rather than decoration. Use :plain for plain labels (no dot).

    <x-admin.badge variant="success">Active</x-admin.badge>
--}}

@php
    $allowed = ['neutral', 'gold', 'success', 'danger', 'warning', 'info'];
    $variant = in_array($variant, $allowed, true) ? $variant : 'neutral';
@endphp

<span {{ $attributes->class(['admin-badge', 'admin-badge-'.$variant, 'is-plain' => ! $dot]) }}>{{ $slot }}</span>