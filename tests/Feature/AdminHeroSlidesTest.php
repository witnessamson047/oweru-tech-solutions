<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\HeroSlide;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHeroSlidesTest extends TestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::where('role', 'admin')->firstOrFail();
        $this->actingAs($this->admin);
    }

    private function testSlideTitle(): string
    {
        return 'Test Slide ' . uniqid();
    }

    /**
     * Fake an image upload from real JPEG bytes (XAMPP PHP has no GD extension).
     */
    private function fakeImage(string $name = 'slide.jpg'): UploadedFile
    {
        $bytes = file_get_contents(public_path('images/hero/team-collaboration.jpg'));

        return UploadedFile::fake()->createWithContent($name, $bytes);
    }

    public function test_index_page_returns_200(): void
    {
        $response = $this->get(route('admin.hero-slides.index'));

        $response->assertStatus(200);
        $response->assertSee('Hero Slides');
    }

    public function test_create_page_returns_200(): void
    {
        $response = $this->get(route('admin.hero-slides.create'));

        $response->assertStatus(200);
        $response->assertSee('Add Hero Slide');
        $response->assertSee('alt_text');
    }

    public function test_store_creates_slide_with_uploaded_image(): void
    {
        Storage::fake('public');
        $title = $this->testSlideTitle();

        $response = $this->post(route('admin.hero-slides.store'), [
            'title' => $title,
            'alt_text' => 'A test image of a dashboard',
            'sort_order' => 10,
            'active' => '1',
            'image' => $this->fakeImage('slide.jpg'),
        ]);

        $response->assertRedirect(route('admin.hero-slides.index'));
        $this->assertDatabaseHas('hero_slides', ['title' => $title, 'active' => true]);

        $slide = HeroSlide::where('title', $title)->firstOrFail();
        $this->assertStringStartsWith('images/hero/', $slide->image_path);
        Storage::disk('public')->assertExists(substr($slide->image_path, strlen('images/')));

        // Cleanup
        Storage::disk('public')->delete(substr($slide->image_path, strlen('images/')));
        $slide->delete();
    }

    public function test_store_requires_image_and_alt_text(): void
    {
        $response = $this->post(route('admin.hero-slides.store'), [
            'title' => $this->testSlideTitle(),
            'sort_order' => 1,
        ]);

        $response->assertSessionHasErrors(['image', 'alt_text']);
    }

    public function test_update_changes_slide_and_replaces_image(): void
    {
        Storage::fake('public');
        $title = $this->testSlideTitle();

        $this->post(route('admin.hero-slides.store'), [
            'title' => $title,
            'alt_text' => 'Original alt',
            'sort_order' => 11,
            'image' => $this->fakeImage('old.jpg'),
        ]);
        $slide = HeroSlide::where('title', $title)->firstOrFail();
        $oldPath = substr($slide->image_path, strlen('images/'));

        $response = $this->put(route('admin.hero-slides.update', $slide), [
            'title' => $title,
            'alt_text' => 'Updated alt text',
            'sort_order' => 12,
            'image' => $this->fakeImage('new.jpg'),
        ]);

        $response->assertRedirect(route('admin.hero-slides.index'));
        $this->assertDatabaseHas('hero_slides', ['id' => $slide->id, 'alt_text' => 'Updated alt text', 'sort_order' => 12]);

        Storage::disk('public')->assertMissing($oldPath);
        $newPath = substr($slide->fresh()->image_path, strlen('images/'));
        Storage::disk('public')->assertExists($newPath);

        // Cleanup
        Storage::disk('public')->delete($newPath);
        $slide->delete();
    }

    public function test_update_keeps_image_when_none_uploaded(): void
    {
        Storage::fake('public');
        $title = $this->testSlideTitle();

        $this->post(route('admin.hero-slides.store'), [
            'title' => $title,
            'alt_text' => 'Alt',
            'sort_order' => 13,
            'image' => $this->fakeImage('keep.jpg'),
        ]);
        $slide = HeroSlide::where('title', $title)->firstOrFail();
        $originalPath = $slide->image_path;

        $response = $this->put(route('admin.hero-slides.update', $slide), [
            'title' => 'Renamed ' . uniqid(),
            'alt_text' => 'Alt',
            'sort_order' => 13,
        ]);

        $response->assertRedirect(route('admin.hero-slides.index'));
        $this->assertSame($originalPath, $slide->fresh()->image_path);

        // Cleanup
        Storage::disk('public')->delete(substr($originalPath, strlen('images/')));
        $slide->delete();
    }

    public function test_destroy_deletes_slide_and_image(): void
    {
        Storage::fake('public');
        $title = $this->testSlideTitle();

        $this->post(route('admin.hero-slides.store'), [
            'title' => $title,
            'alt_text' => 'Alt',
            'sort_order' => 14,
            'image' => $this->fakeImage('doomed.jpg'),
        ]);
        $slide = HeroSlide::where('title', $title)->firstOrFail();
        $path = substr($slide->image_path, strlen('images/'));

        $response = $this->delete(route('admin.hero-slides.destroy', $slide));

        $response->assertRedirect(route('admin.hero-slides.index'));
        $this->assertDatabaseMissing('hero_slides', ['id' => $slide->id]);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_homepage_renders_slides_from_database(): void
    {
        $slide = HeroSlide::where('active', true)->firstOrFail();

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee($slide->title);
        $response->assertSee('images/hero/');
    }

    public function test_homepage_ignores_inactive_slides(): void
    {
        $title = $this->testSlideTitle();
        $slide = HeroSlide::create([
            'image_path' => 'images/hero/team-collaboration.jpg',
            'title' => $title,
            'alt_text' => 'Hidden slide alt',
            'sort_order' => 99,
            'active' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee($title);

        $slide->delete();
    }
}
