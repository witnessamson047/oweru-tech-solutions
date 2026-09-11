<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_slides', function (Blueprint $table) {
            $table->id();
            $table->string('image_path'); // public-dir relative, e.g. images/hero/developer-coding.jpg
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('alt_text');
            $table->integer('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Seed with the slides currently hardcoded in the hero carousel
        DB::table('hero_slides')->insert([
            [
                'image_path' => 'images/hero/scanner-analytics.jpg',
                'title' => 'Website Health Scanner — real diagnostics, instant score',
                'subtitle' => null,
                'alt_text' => 'Analytics charts on a laptop screen showing a website performance report',
                'sort_order' => 1,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image_path' => 'images/hero/developer-coding.jpg',
                'title' => 'Custom Software — designed, built and shipped by us',
                'subtitle' => null,
                'alt_text' => 'Developer writing code on a laptop in an office',
                'sort_order' => 2,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'image_path' => 'images/hero/team-collaboration.jpg',
                'title' => 'The Oweru Team — real people, real results',
                'subtitle' => null,
                'alt_text' => 'Our team collaborating around a table in the office',
                'sort_order' => 3,
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_slides');
    }
};
