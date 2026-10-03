@props([
    'paginator',
    'perPage' => null,
    'label' => 'results',
    'showInfo' => true,
])

{{--
    The ONE pagination bar for the admin panel. Wraps Laravel's paginator and
    renders: "Showing 1–20 of 143 results" on the left, page links on the right.

    Filters survive page changes automatically — the caller is responsible for
    ->withQueryString() on the controller's paginate(), which every list does.

    @props int $paginator A LengthAwarePaginator / Paginator from ->paginate()
--}}

@if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
    @php
        $from = $paginator->firstItem() ?? 0;
        $to = $paginator->lastItem() ?? 0;
        $total = method_exists($paginator, 'total') ? $paginator->total() : $to;
        $current = $paginator->currentPage();
        $last = $paginator->lastPage();
        // Window of page links around the current page, clamped to the edges.
        $window = 2;
        $start = max(1, $current - $window);
        $end = min($last, $current + $window);
    @endphp

    <div class="admin-pagination">
        @if ($showInfo)
            <p class="admin-pagination-info admin-tabular">
                @if ($total > 0)
                    Showing <span class="font-bold">{{ $from }}–{{ $to }}</span>
                    of <span class="font-bold">{{ number_format($total) }}</span> {{ $label }}
                    @if ($last > 1)
                        <span class="opacity-70">· page {{ $current }} of {{ $last }}</span>
                    @endif
                @else
                    No {{ $label }} to show
                @endif
            </p>
        @endif

        @if ($last > 1)
            <nav class="admin-pagination-links" role="navigation" aria-label="Pagination">
                <a href="{{ $paginator->previousPageUrl() ?? '#' }}"
                   class="admin-page-link {{ $paginator->onFirstPage() ? 'is-disabled' : '' }}"
                   rel="prev" aria-label="Previous page">
                    <x-admin.icon name="arrow-left" />
                </a>

                @if ($start > 1)
                    <a href="{{ $paginator->url(1) }}" class="admin-page-link">1</a>
                    @if ($start > 2)
                        <span class="admin-page-link is-disabled" aria-hidden="true">…</span>
                    @endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    @if ($page === $current)
                        <span class="admin-page-link is-active" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="admin-page-link">{{ $page }}</a>
                    @endif
                @endfor

                @if ($end < $last)
                    @if ($end < $last - 1)
                        <span class="admin-page-link is-disabled" aria-hidden="true">…</span>
                    @endif
                    <a href="{{ $paginator->url($last) }}" class="admin-page-link">{{ $last }}</a>
                @endif

                <a href="{{ $paginator->nextPageUrl() ?? '#' }}"
                   class="admin-page-link {{ $paginator->hasMorePages() ? '' : 'is-disabled' }}"
                   rel="next" aria-label="Next page">
                    <x-admin.icon name="arrow-right" />
                </a>
            </nav>
        @endif
    </div>
@endif