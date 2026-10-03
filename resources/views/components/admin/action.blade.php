@props([
    'href' => null,
    'action' => null,
    'method' => 'POST',
    'variant' => 'default',
    'icon' => null,
    'confirm' => null,
    'title' => null,
])

{{--
    One small row/button action. Renders as a link when :href is set, or as a
    form button when :action is set — so Edit / Delete / Run Now all look and
    behave the same on every page.

    <x-admin.action :href="route('...edit', $row)" icon="pencil">Edit</x-admin.action>
    <x-admin.action :action="route('...destroy', $row)" method="DELETE"
                    icon="trash" variant="danger" :confirm="'Delete this?'">Delete</x-admin.action>
--}}

@php
    $classes = ['admin-action'];
    if ($variant === 'primary') {
        $classes[] = 'is-primary';
    } elseif ($variant === 'danger') {
        $classes[] = 'is-danger';
    } elseif ($variant === 'gold') {
        $classes[] = 'is-gold';
    }
    if ($icon) {
        $classes[] = 'has-icon';
    }
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }} @if($title) title="{{ $title }}" @endif>
        @if ($icon)<x-admin.icon :name="$icon" />@endif
        {{ $slot }}
    </a>
@else
    <form method="POST" action="{{ $action }}" class="inline"
          @if ($confirm) onsubmit="return confirm(@js($confirm))" @endif>
        @csrf
        @if ($method !== 'POST')
            @method($method)
        @endif
        <button type="submit" {{ $attributes->class($classes) }} @if($title) title="{{ $title }}" @endif>
            @if ($icon)<x-admin.icon :name="$icon" />@endif
            {{ $slot }}
        </button>
    </form>
@endif