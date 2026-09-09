<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageExclusion;
use Illuminate\Http\Request;

class PackageExclusionController extends Controller
{
    public function index()
    {
        $exclusions = PackageExclusion::orderBy('sort_order')->get();
        return view('admin.package-exclusions.index', compact('exclusions'));
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
