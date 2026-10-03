<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Concerns\SortsListings;
use App\Models\Recommendation;
use App\Models\ScannerCheck;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    use SortsListings;

    public function index(Request $request)
    {
        // Columns staff can order by. check_name is the join key used by the
        // engine, so it is worth being able to sort by it explicitly.
        $sort = $this->resolveSort($request, [
            'check_name' => 'Check',
            'service_type' => 'Service',
            'priority' => 'Priority',
            'area' => 'Area',
        ], default: 'area');

        $query = Recommendation::query();

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('check_name', 'like', "%{$search}%")
                    ->orWhere('solution', 'like', "%{$search}%")
                    ->orWhere('service_type', 'like', "%{$search}%")
                    ->orWhere('finding_example', 'like', "%{$search}%");
            });
        }

        if ($area = $request->query('area')) {
            $query->where('area', $area);
        }

        if ($service = $request->query('service')) {
            $query->where('service_type', $service);
        }

        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        $this->applySort($query, $sort, ['check_name', 'service_type', 'area']);

        $recommendations = $query->paginate($this->perPage($request, 20))->withQueryString();

        // Counts for the filter dropdowns and the summary cards. These come
        // from the full table, not the filtered page, so the options never
        // disappear just because the current filter is narrow.
        $stats = [
            'total' => Recommendation::count(),
            'active' => Recommendation::where('active', true)->count(),
            'categories' => Recommendation::distinct('service_type')->count('service_type'),
        ];

        $serviceTypes = Recommendation::query()
            ->distinct()
            ->orderBy('service_type')
            ->pluck('service_type');
        $scannerAreas = ScannerCheck::query()
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        return view('admin.recommendations.index', compact(
            'recommendations',
            'stats',
            'serviceTypes',
            'scannerAreas',
            'sort',
        ));
    }

    public function create()
    {
        return view('admin.recommendations.create', [
            'recommendation' => new Recommendation(),
            'checks' => $this->checkNames(),
            'areas' => ScannerCheck::AREAS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'check_name' => 'required|string|max:255',
            'area' => 'required|string',
            'finding_example' => 'nullable|string',
            'consequence' => 'nullable|string',
            'solution' => 'required|string',
            'service_type' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);

        // New mappings start active — an inactive mapping would silently do nothing.
        $validated['active'] = true;

        Recommendation::create($validated);

        return redirect()->route('admin.recommendations.index')
            ->with('success', 'Mapping created — it will now attach to any scan that fails "'.$validated['check_name'].'".');
    }

    public function edit(Recommendation $recommendation)
    {
        return view('admin.recommendations.edit', [
            'recommendation' => $recommendation,
            'checks' => $this->checkNames(),
            'areas' => ScannerCheck::AREAS,
        ]);
    }

    public function update(Request $request, Recommendation $recommendation)
    {
        $validated = $request->validate([
            'check_name' => 'required|string|max:255',
            'area' => 'required|string',
            'finding_example' => 'nullable|string',
            'consequence' => 'nullable|string',
            'solution' => 'required|string',
            'service_type' => 'required|string',
            'priority' => 'required|in:low,medium,high',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active');

        $recommendation->update($validated);

        return redirect()->route('admin.recommendations.index')->with('success', 'Mapping updated.');
    }

    public function destroy(Recommendation $recommendation)
    {
        $name = $recommendation->check_name;
        $recommendation->delete();

        return redirect()->route('admin.recommendations.index')
            ->with('success', 'Deleted the mapping for "'.$name.'".');
    }

    /**
     * Just the check names for the datalist — the create/edit forms do not
     * need a whole ScannerCheck model hydrated per row.
     */
    private function checkNames()
    {
        return ScannerCheck::orderBy('area')->orderBy('name')->pluck('name', 'name');
    }
}
