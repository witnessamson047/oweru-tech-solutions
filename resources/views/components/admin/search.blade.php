@props([
    'value' => null,
    'placeholder' => 'Search…',
    'label' => 'Search',
    'icon' => 'search',
])

{{--
    A search input for the filter bar. Submits via GET so the result is a
    bookmarkable URL that pagination can preserve. The clear button only shows
    once there is something to clear.

    @props string|null $value Current search term (usually request('search'))
--}}

<div class="relative flex-1 min-w-[12rem]">
    <span class="pointer-events-none absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
        <x-admin.icon :name="$icon" class="w-4 h-4" />
    </span>
    <input type="search"
           name="search"
           value="{{ $value }}"
           placeholder="{{ $placeholder }}"
           aria-label="{{ $label }}"
           class="admin-filter-input !pl-8 @if ($value) !pr-8 @endif">
    @if ($value)
        <a href="{{ request()->fullUrlWithoutQuery(['search', 'page']) }}"
           class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-700 transition"
           title="Clear search" aria-label="Clear search">
            <x-admin.icon name="x-circle" class="w-4 h-4" />
        </a>
    @endif
</div>