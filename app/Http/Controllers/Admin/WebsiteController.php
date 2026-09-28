<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Support\UrlInput;
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
            'url' => 'required|string|max:2048',
            'sector' => 'nullable|string|max:255',
        ]);

        // Forgiving input: "abc.co.tz" works just like "https://abc.co.tz"
        $url = UrlInput::normalize($validated['url']);

        if (! $url) {
            return redirect()
                ->route('admin.websites.create')
                ->with('error', UrlInput::friendlyError())
                ->withErrors(['url' => UrlInput::friendlyError()])
                ->withInput();
        }

        if (Website::where('url', $url)->exists()) {
            return redirect()
                ->route('admin.websites.create')
                ->with('error', 'A website with this address already exists.')
                ->withInput();
        }

        Website::create([
            'business_name' => $validated['business_name'],
            'url' => $url,
            'sector' => $validated['sector'] ?? null,
            'status' => 'active',
            'exclusion_status' => 'active',
        ]);

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
            'url' => 'required|string|max:2048',
            'sector' => 'nullable|string|max:255',
        ]);

        // Forgiving input: "abc.co.tz" works just like "https://abc.co.tz"
        $url = UrlInput::normalize($validated['url']);

        if (! $url) {
            return redirect()
                ->route('admin.websites.edit', $website)
                ->with('error', UrlInput::friendlyError())
                ->withErrors(['url' => UrlInput::friendlyError()])
                ->withInput();
        }

        if (Website::where('url', $url)->where('id', '!=', $website->id)->exists()) {
            return redirect()
                ->route('admin.websites.edit', $website)
                ->with('error', 'A website with this address already exists.')
                ->withInput();
        }

        $website->update([
            'business_name' => $validated['business_name'],
            'url' => $url,
            'sector' => $validated['sector'] ?? null,
        ]);

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
