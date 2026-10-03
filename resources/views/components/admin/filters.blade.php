@props([
    'action' => null,
    'reset' => null,
    'submitLabel' => 'Apply',
    'method' => 'GET',
])

{{--
    The filter bar every list page shares: a GET form with search + selects on
    the left and Apply / Reset on the right. Because it is GET, the filtered
    state lives in the URL, so pagination, sorting and the browser Back button
    all behave the way staff expect.

    Drop the fields straight into the slot; each one is a .field group.

    @props string|null $reset URL for the "Reset" link (usually the bare index)
--}}

<form method="{{ $method }}" {{ $action ? 'action="'.$action.'"' : '' }}
      class="admin-filters" role="search">
    {{ $slot }}

    <div class="spacer"></div>

    <div class="flex items-center gap-2">
        <button type="submit" class="admin-btn">
            <x-admin.icon name="filter" class="w-4 h-4" />
            {{ $submitLabel }}
        </button>
        @if ($reset && request()->query())
            <a href="{{ $reset }}" class="admin-btn-ghost">Reset</a>
        @endif
    </div>
</form>