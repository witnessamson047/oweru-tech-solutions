<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scraped_businesses', function (Blueprint $table) {
            // Preliminary gaps/opportunities detected by the scraper itself
            // at scrape time (before any health scan). Structured JSON:
            // [{check_name, gap, opportunity}, ...] where check_name matches
            // the scanner's checks so the recommendations mapping attaches.
            $table->json('preliminary_gaps')->nullable()->after('weaknesses');
        });
    }

    public function down(): void
    {
        Schema::table('scraped_businesses', function (Blueprint $table) {
            $table->dropColumn('preliminary_gaps');
        });
    }
};
