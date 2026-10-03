<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared list-page sorting.
 *
 * Every admin index used to hard-code its own ORDER BY, which made it
 * impossible for staff to put the most relevant rows first (e.g. oldest
 * un-contacted enquiry at the top). This trait gives all of them the same
 * ?sort=&direction= contract with a strict allow-list, so a tampered query
 * string can never reach the database as a column name.
 *
 * Usage in a controller:
 *
 *     use SortsListings;
 *
 *     public function index(Request $request)
 *     {
 *         $sort = $this->resolveSort($request, [
 *             'created_at' => 'Newest first',
 *             'name'       => 'Name A–Z',
 *         ], default: 'created_at');
 *
 *         $query->tap(fn (Builder $q) => $this->applySort($q, $sort));
 *         ...
 *     }
 *
 * The blade then renders headers with <x-admin.sort column="name" label="Name" ... />
 * which reads the same request keys.
 */
trait SortsListings
{
    /**
     * Resolve ?sort= / ?direction= against the allow-list.
     *
     * @param  array<string, string>  $allowed    column => human label (used for headings)
     * @param  string  $default  column used when ?sort is absent or not allowed
     * @return array{column: string, label: string, direction: string, default: string}
     */
    protected function resolveSort(
        \Illuminate\Http\Request $request,
        array $allowed,
        string $default = 'created_at',
        string $defaultDirection = 'desc',
    ): array {
        $requested = (string) $request->query('sort', '');
        $column = array_key_exists($requested, $allowed) ? $requested : $default;

        if (! array_key_exists($column, $allowed)) {
            $column = array_key_first($allowed);
        }

        $direction = strtolower((string) $request->query('direction', $defaultDirection)) === 'asc'
            ? 'asc'
            : 'desc';

        return [
            'column' => $column,
            'label' => $allowed[$column],
            'direction' => $direction,
            'default' => $default,
            'defaultDirection' => $defaultDirection,
        ];
    }

    /**
     * Apply a resolved sort to a query, honouring an optional per-column
     * direction map so, for example, a name column can always ascend while
     * dates default to descending.
     *
     * @param  array<string, string>  $ascendingOnly  columns that must sort ASC
     */
    protected function applySort(Builder $query, array $sort, array $ascendingOnly = []): Builder
    {
        $direction = in_array($sort['column'], $ascendingOnly, true)
            ? 'asc'
            : $sort['direction'];

        return $query->orderBy($sort['column'], $direction);
    }

    /**
     * Default per-page size for admin lists. Kept in one place so the list
     * pages stay consistent and a change does not have to touch 15 controllers.
     */
    protected function perPage(\Illuminate\Http\Request $request, int $default = 20, int $max = 100): int
    {
        $requested = (int) $request->query('per_page', $default);

        return max(5, min($requested, $max));
    }
}