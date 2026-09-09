<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Recommendation;
use App\Models\ScannerCheck;
use Illuminate\Http\Request;

class RecommendationController extends Controller
{
    public function index()
    {
        $recommendations = Recommendation::orderBy('area')->orderBy('priority')->get();

        $stats = [
            'total' => Recommendation::count(),
            'active' => Recommendation::where('active', true)->count(),
            'categories' => Recommendation::distinct('service_type')->count('service_type'),
        ];

        return view('admin.recommendations.index', compact('recommendations', 'stats'));
    }

    public function create()
    {
        return view('admin.recommendations.create', [
            'recommendation' => new Recommendation(),
            'checks' => ScannerCheck::all(),
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

        $validated['active'] = true;

        Recommendation::create($validated);

        return redirect()->route('admin.recommendations.index')->with('success', 'Recommendation mapping created.');
    }

    public function edit(Recommendation $recommendation)
    {
        return view('admin.recommendations.edit', [
            'recommendation' => $recommendation,
            'checks' => ScannerCheck::all(),
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
        ]);

        $recommendation->update($validated);

        return redirect()->route('admin.recommendations.index')->with('success', 'Recommendation updated.');
    }

    public function destroy(Recommendation $recommendation)
    {
        $recommendation->delete();
        return redirect()->route('admin.recommendations.index')->with('success', 'Recommendation deleted.');
    }
}
