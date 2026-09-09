<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CarePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarePlanController extends Controller
{
    public function index()
    {
        $carePlans = CarePlan::orderBy('created_at')->get();
        return view('admin.care-plans.index', compact('carePlans'));
    }

    public function create()
    {
        return view('admin.care-plans.create', ['carePlan' => new CarePlan()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_tzs' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'is_featured' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['active'] = $request->boolean('active', true);

        CarePlan::create($validated);

        return redirect()->route('admin.care-plans.index')->with('success', 'Care plan created successfully.');
    }

    public function edit(CarePlan $carePlan)
    {
        return view('admin.care-plans.edit', ['carePlan' => $carePlan]);
    }

    public function update(Request $request, CarePlan $carePlan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_tzs' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'is_featured' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['active'] = $request->boolean('active', true);

        $carePlan->update($validated);

        return redirect()->route('admin.care-plans.index')->with('success', 'Care plan updated successfully.');
    }

    public function destroy(CarePlan $carePlan)
    {
        $carePlan->delete();
        return redirect()->route('admin.care-plans.index')->with('success', 'Care plan deleted.');
    }
}
