@props([
    'title' => null,
    'subtitle' => null,
    'icon' => null,
    'padded' => true,
    'interactive' => false,
    'href' => null,
])

{{--
    The one panel shape used across the panel: optional header (title, subtitle,
    icon), an optional action slot, then the body. Interactive cards lift on
    hover; static panels stay still so the page has a clear hierarchy.
--}}

@php
    $tag = $href ? 'a' : 'div';
    $paddingClass = $padded ? 'p-5' : '';
@endphp

<{{ $tag }} @if($href) href="{{ $href }}" @endif
    {{ $attributes->class([
        'admin-card block',
        'admin-card-interactive' => $interactive || $href,
    ]) }}
>
    @if($title || isset($actions))
        <div class="admin-panel-head">
            <div class="flex items-start gap-2.5 min-w-0">
                @if($icon)
                    <span class="admin-stat-icon shrink-0" style="width:2rem;height:2rem">
                        <x-admin.icon :name="$icon" class="w-4 h-4" />
                    </span>
                @endif
                <div class="min-w-0">
                    @if($title)
                        <h3 class="admin-panel-title">{{ $title }}</h3>
                    @endif
                    @if($subtitle)
                        <p class="admin-panel-sub">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            @isset($actions)
                <div class="flex items-center gap-2 shrink-0">{{ $actions }}</div>
            @endisset
        </div>
    @endif

    <div class="{{ $paddingClass }}">{{ $slot }}</div>

    @isset($footer)
        <div class="admin-panel-foot">{{ $footer }}</div>
    @endisset
</{{ $tag }}>