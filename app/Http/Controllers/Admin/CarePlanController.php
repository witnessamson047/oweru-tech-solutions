<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Concerns\SortsListings;
use App\Models\CarePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarePlanController extends Controller
{
    use SortsListings;

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request, [
            'name' => 'Plan',
            'price_tzs' => 'TZS price',
            'price_usd' => 'USD price',
            'created_at' => 'Newest',
        ], default: 'price_tzs');

        $query = CarePlan::query();

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $this->applySort($query, $sort, ['name']);

        $carePlans = $query->paginate($this->perPage($request, 20))->withQueryString();

        $stats = [
            'total' => CarePlan::count(),
            'active' => CarePlan::where('active', true)->count(),
            'featured' => CarePlan::where('is_featured', true)->count(),
            'monthly_tzs' => (int) CarePlan::where('active', true)->sum('price_tzs'),
            'monthly_usd' => (int) CarePlan::where('active', true)->sum('price_usd'),
        ];

        return view('admin.care-plans.index', compact('carePlans', 'stats', 'sort'));
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
