<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Concerns\SortsListings;
use App\Models\PackageExclusion;
use Illuminate\Http\Request;

class PackageExclusionController extends Controller
{
    use SortsListings;

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request, [
            'sort_order' => 'Order',
            'description' => 'Description',
        ], default: 'sort_order', defaultDirection: 'asc');

        $query = PackageExclusion::query();

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%");
            });
        }

        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        $this->applySort($query, $sort);

        $exclusions = $query->paginate($this->perPage($request, 25))->withQueryString();

        $stats = [
            'total' => PackageExclusion::count(),
            'active' => PackageExclusion::where('active', true)->count(),
        ];

        return view('admin.package-exclusions.index', compact('exclusions', 'stats', 'sort'));
    }

    public function create()
    {
        return view('admin.package-exclusions.create', ['exclusion' => new PackageExclusion()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'details' => 'nullable|string',
            'sort_order' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);

        PackageExclusion::create($validated);

        return redirect()->route('admin.package-exclusions.index')->with('success', 'Exclusion created.');
    }

    public function edit(PackageExclusion $packageExclusion)
    {
        return view('admin.package-exclusions.edit', compact('packageExclusion'));
    }

    public function update(Request $request, PackageExclusion $packageExclusion)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'details' => 'nullable|string',
            'sort_order' => 'required|integer|min:0',
            'active' => 'boolean',
        ]);

        $validated['active'] = $request->boolean('active', true);

        $packageExclusion->update($validated);

        return redirect()->route('admin.package-exclusions.index')->with('success', 'Exclusion updated.');
    }

    public function destroy(PackageExclusion $packageExclusion)
    {
        $packageExclusion->delete();
        return redirect()->route('admin.package-exclusions.index')->with('success', 'Exclusion deleted.');
    }
}
