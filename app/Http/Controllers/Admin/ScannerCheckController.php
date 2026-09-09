<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScannerCheck;
use Illuminate\Http\Request;

class ScannerCheckController extends Controller
{
    public function index(Request $request)
    {
        $query = ScannerCheck::withCount('results');

        if ($area = $request->input('area')) {
            $query->where('area', $area);
        }

        if ($enabled = $request->input('enabled')) {
            $query->where('enabled', $enabled === 'true');
        }

        $checks = $query->orderBy('area')->orderBy('weight')->paginate(20);

        $areas = ScannerCheck::AREAS;

        return view('admin.scanner-checks.index', compact('checks', 'areas'));
    }

    public function create()
    {
        $areas = ScannerCheck::AREAS;

        return view('admin.scanner-checks.create', compact('areas'));
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
