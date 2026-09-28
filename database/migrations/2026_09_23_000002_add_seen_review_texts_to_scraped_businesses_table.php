<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scraped_businesses', function (Blueprint $table) {
            // Normalized fingerprints of reviews the watchdog has already
            // reported, so scheduled re-scrapes only surface NEW reviews.
            $table->json('seen_review_texts')->nullable()->after('reviews');
        });
    }

    public function down(): void
    {
        Schema::table('scraped_businesses', function (Blueprint $table) {
            $table->dropColumn('seen_review_texts');
        });
    }
};
