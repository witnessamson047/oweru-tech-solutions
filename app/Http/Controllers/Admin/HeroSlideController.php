<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HeroSlideController extends Controller
{
    public function index()
    {
        $heroSlides = HeroSlide::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.hero-slides.index', compact('heroSlides'));
    }

    public function create()
    {
        return view('admin.hero-slides.create', ['heroSlide' => new HeroSlide()]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateSlide($request);

        $validated['image_path'] = $this->storeImage($request);
        $validated['active'] = $request->boolean('active', true);
        $validated['subtitle'] = $validated['subtitle'] ?? null;

        HeroSlide::create($validated);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide created successfully.');
    }

    public function edit(HeroSlide $heroSlide)
    {
        return view('admin.hero-slides.edit', compact('heroSlide'));
    }

    public function update(Request $request, HeroSlide $heroSlide)
    {
        $validated = $this->validateSlide($request, $heroSlide);

        if ($request->hasFile('image')) {
            $this->deleteImage($heroSlide->image_path);
            $validated['image_path'] = $this->storeImage($request);
        }

        $validated['active'] = $request->boolean('active', true);

        $heroSlide->update($validated);

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide updated successfully.');
    }

    public function destroy(HeroSlide $heroSlide)
    {
        $this->deleteImage($heroSlide->image_path);
        $heroSlide->delete();

        return redirect()->route('admin.hero-slides.index')
            ->with('success', 'Hero slide deleted.');
    }

    private function validateSlide(Request $request, ?HeroSlide $existing = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'alt_text' => 'required|string|max:255',
            'sort_order' => 'required|integer|min:0|max:9999',
            'image' => $existing ? 'nullable|image|max:4096' : 'required|image|max:4096',
        ]);
    }

    private function storeImage(Request $request): string
    {
        $path = $request->file('image')->store('hero', 'public');

        return 'images/' . $path;
    }

    private function deleteImage(string $path): void
    {
        // Only delete files managed by the uploader (storage disk), never seeded public/ assets
        if (str_starts_with($path, 'images/hero/')) {
            Storage::disk('public')->delete(substr($path, strlen('images/')));
        }
    }
}
