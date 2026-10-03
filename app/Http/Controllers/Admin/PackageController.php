<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Concerns\SortsListings;
use App\Models\ServicePackage;
use App\Models\ServiceLine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    use SortsListings;

    public function index(Request $request)
    {
        $sort = $this->resolveSort($request, [
            'name' => 'Package',
            'price_tzs' => 'TZS price',
            'price_usd' => 'USD price',
            'delivery_days' => 'Delivery time',
        ], default: 'price_tzs');

        $query = ServicePackage::query()->with('serviceLine');

        if ($search = trim((string) $request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // The catalogue is browsed by audience first — that is how the public
        // site presents it — so keep the group as the primary grouping.
        if ($group = $request->query('group')) {
            $query->where('group', $group);
        }

        if ($request->query('status') === 'active') {
            $query->where('active', true);
        } elseif ($request->query('status') === 'inactive') {
            $query->where('active', false);
        }

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        if ($line = $request->query('service_line')) {
            $query->where('service_line_id', $line);
        }

        $this->applySort($query, $sort, ['name']);

        $packages = $query->paginate($this->perPage($request, 20))->withQueryString();

        $stats = [
            'total' => ServicePackage::count(),
            'active' => ServicePackage::where('active', true)->count(),
            'featured' => ServicePackage::where('is_featured', true)->count(),
        ];

        return view('admin.packages.index', [
            'packages' => $packages,
            'stats' => $stats,
            'sort' => $sort,
            'groups' => ['individuals' => 'Individuals & Professionals', 'sme' => 'Small & Medium Enterprises', 'corporate' => 'Business & Corporate'],
            'serviceLines' => ServiceLine::active()->get(),
        ]);
    }

    public function create()
    {
        return view('admin.packages.create', [
            'package' => new ServicePackage(),
            'serviceLines' => ServiceLine::active()->get(),
            'groups' => ['individuals' => 'Individuals & Professionals', 'sme' => 'Small & Medium Enterprises', 'corporate' => 'Business & Corporate'],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'required|in:individuals,sme,corporate',
            'description' => 'nullable|string',
            'price_tzs' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'delivery_days' => 'required|integer|min:1',
            'service_line_id' => 'nullable|exists:service_lines,id',
            'is_featured' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['active'] = $request->boolean('active', true);

        ServicePackage::create($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package created successfully.');
    }

    public function edit(ServicePackage $package)
    {
        return view('admin.packages.edit', [
            'package' => $package,
            'serviceLines' => ServiceLine::active()->get(),
            'groups' => ['individuals' => 'Individuals & Professionals', 'sme' => 'Small & Medium Enterprises', 'corporate' => 'Business & Corporate'],
        ]);
    }

    public function update(Request $request, ServicePackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'group' => 'required|in:individuals,sme,corporate',
            'description' => 'nullable|string',
            'price_tzs' => 'required|numeric|min:0',
            'price_usd' => 'required|numeric|min:0',
            'delivery_days' => 'required|integer|min:1',
            'service_line_id' => 'nullable|exists:service_lines,id',
            'is_featured' => 'boolean',
            'active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['active'] = $request->boolean('active', true);

        $package->update($validated);

        return redirect()->route('admin.packages.index')->with('success', 'Package updated successfully.');
    }

    public function destroy(ServicePackage $package)
    {
        $package->delete();
        return redirect()->route('admin.packages.index')->with('success', 'Package deleted.');
    }
}
