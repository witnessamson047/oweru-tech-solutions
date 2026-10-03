<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Concerns\SortsListings;
use App\Models\ScannerCheck;
use Illuminate\Http\Request;

class ScannerCheckController extends Controller
{
    use SortsListings;

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request, [
            'name' => 'Check',
            'area' => 'Area',
            'weight' => 'Weight',
        ], default: 'area', defaultDirection: 'asc');

        $query = ScannerCheck::withCount('results');

        if ($area = $request->input('area')) {
            $query->where('area', $area);
        }

        if ($enabled = $request->input('enabled')) {
            $query->where('enabled', $enabled === 'true');
        }

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $this->applySort($query, $sort, ['name']);

        // Break ties deterministically so pagination never shuffles rows.
        $query->orderBy('weight');

        $checks = $query->paginate($this->perPage($request, 20))->withQueryString();

        $areas = ScannerCheck::AREAS;

        return view('admin.scanner-checks.index', [
            'checks' => $checks,
            'areas' => $areas,
            'sort' => $sort,
            'stats' => [
                'total' => ScannerCheck::count(),
                'enabled' => ScannerCheck::where('enabled', true)->count(),
                'disabled' => ScannerCheck::where('enabled', false)->count(),
            ],
        ]);
    }

    public function create()
    {
        $areas = ScannerCheck::AREAS;

        return view('admin.scanner-checks.create', [
            'scannerCheck' => new ScannerCheck(),
            'areas' => $areas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:scanner_checks,slug',
            'area' => 'required|in:' . implode(',', ScannerCheck::AREAS),
            'weight' => 'required|integer|min:1|max:100',
            'enabled' => 'nullable|boolean',
            'description' => 'nullable|string',
            'wording_pass' => 'nullable|string',
            'wording_fail' => 'nullable|string',
        ]);

        ScannerCheck::create($validated);

        return redirect()->route('admin.scanner-checks.index')
            ->with('success', 'Scanner check created successfully.');
    }

    public function edit(ScannerCheck $scannerCheck)
    {
        $areas = ScannerCheck::AREAS;

        return view('admin.scanner-checks.edit', compact('scannerCheck', 'areas'));
    }

    public function update(Request $request, ScannerCheck $scannerCheck)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:scanner_checks,slug,' . $scannerCheck->id,
            'area' => 'required|in:' . implode(',', ScannerCheck::AREAS),
            'weight' => 'required|integer|min:1|max:100',
            'enabled' => 'nullable|boolean',
            'description' => 'nullable|string',
            'wording_pass' => 'nullable|string',
            'wording_fail' => 'nullable|string',
        ]);

        $scannerCheck->update($validated);

        return redirect()->route('admin.scanner-checks.index')
            ->with('success', 'Scanner check updated successfully.');
    }

    public function destroy(ScannerCheck $scannerCheck)
    {
        $scannerCheck->delete();

        return redirect()->route('admin.scanner-checks.index')
            ->with('success', 'Scanner check deleted successfully.');
    }
}
