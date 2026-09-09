<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePackage;
use App\Models\ServiceLine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PackageController extends Controller
{
    public function index()
    {
        $packages = ServicePackage::orderBy('group')->orderBy('sort_order')->get();
        return view('admin.packages.index', compact('packages'));
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
