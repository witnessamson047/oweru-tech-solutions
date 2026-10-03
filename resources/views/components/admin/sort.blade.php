@props([
    'column',
    'label',
    'sortable' => true,
    'activeSort' => null,
    'activeDirection' => 'desc',
    'defaultDirection' => 'asc',
])

{{--
    A sortable column header link. Keeps the current filters and toggles
    direction when the same column is clicked again.

    Why the URL is rebuilt by hand: `fullUrlWithQuery(['sort' => null])` does
    NOT drop an existing ?sort from the current URL — it merges. So clicking an
    already-sorted header left the query unchanged and the list never flipped.
    We remove sort/direction/page from the current query first, then re-add.

    @props string $column            query-string key for ?sort
    @props string $label             visible header text
    @props string $defaultDirection  first-click direction ('asc' or 'desc')
--}}

@php
    use Illuminate\Support\Arr;

    $isActive = $activeSort === $column;

    $nextDirection = $isActive
        ? ($activeDirection === 'asc' ? 'desc' : 'asc')
        : ($defaultDirection === 'desc' ? 'desc' : 'asc');

    $arrowDirection = $isActive ? $activeDirection : $nextDirection;

    $params = Arr::except(request()->query(), ['sort', 'direction', 'page']);

    if (! $isActive) {
        $params['sort'] = $column;
        $params['direction'] = $nextDirection;
    }

    $url = request()->url() . (count($params) ? '?' . Arr::query($params) : '');
@endphp

@if ($sortable)
    <a href="{{ $url }}"
       class="admin-sort {{ $isActive ? 'is-active' : '' }} {{ $arrowDirection === 'desc' ? 'is-desc' : '' }}"
       title="Sort by {{ strtolower($label) }}"
       aria-sort="{{ $isActive ? ($arrowDirection === 'asc' ? 'ascending' : 'descending') : 'none' }}">
        {{ $label }}
        <x-admin.icon name="sort" />
    </a>
@else
    {{ $label }}
@endif
