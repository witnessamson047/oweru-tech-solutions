<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    public function index(Request $request)
    {
        $query = Website::withCount('scans')->with('latestScan');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                  ->orWhere('url', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            if ($status === 'excluded') {
                $query->where('exclusion_status', 'excluded');
            } else {
                $query->where('status', $status);
            }
        }

        $websites = $query->latest()->paginate(20);

        return view('admin.websites.index', compact('websites'));
    }

    public function create()
    {
        return view('admin.websites.create', ['website' => new Website()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'url' => 'required|url|unique:websites,url',
            'sector' => 'nullable|string|max:255',
        ]);

        $validated['status'] = 'active';
        $validated['exclusion_status'] = 'active';

        Website::create($validated);

        return redirect()->route('admin.websites.index')->with('success', 'Website added successfully.');
    }

    public function show(Website $website)
    {
        $website->load(['scans' => function ($q) {
            $q->with('results')->latest();
        }, 'latestScan', 'enquiry']);

        return view('admin.websites.show', compact('website'));
    }

    public function edit(Website $website)
    {
        return view('admin.websites.edit', compact('website'));
    }

    public function update(Request $request, Website $website)
    {
        $validated = $request->validate([
            'business_name' => 'required|string|max:255',
            'url' => 'required|url|unique:websites,url,' . $website->id,
            'sector' => 'nullable|string|max:255',
        ]);

        $website->update($validated);

        return redirect()->route('admin.websites.show', $website)->with('success', 'Website updated successfully.');
    }

    public function destroy(Website $website)
    {
        $website->delete();
        return redirect()->route('admin.websites.index')->with('success', 'Website deleted.');
    }

    public function exclude(Website $website)
    {
        $website->update([
            'exclusion_status' => 'excluded',
            'exclusion_reason' => 'Excluded by admin',
        ]);

        return redirect()->back()->with('success', 'Website excluded from scanning.');
    }
}
