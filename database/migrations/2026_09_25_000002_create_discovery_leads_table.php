<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Businesses found by discovery runs that have NO website at all.
        // These cannot enter the scrape/scan pipeline (nothing to scrape) —
        // they are the "we'll build you a website" outreach list. Deduped by
        // name+city so repeated discovery runs never duplicate a lead.
        Schema::create('discovery_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discovery_run_id')->nullable()->constrained()->nullOnDelete();
            $table->string('business_name');
            $table->string('city');
            $table->string('category')->default('all');
            $table->string('osm_type')->nullable();
            $table->double('lat')->nullable();
            $table->double('lon')->nullable();
            $table->string('status')->default('new'); // new | contacted
            $table->timestamp('contacted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['business_name', 'city']);
            $table->index(['status', 'created_at']);
        });

        // How many no-website leads each run contributed (dedupe-aware).
        Schema::table('discovery_runs', function (Blueprint $table) {
            $table->unsignedInteger('leads_count')->default(0)->after('skipped_count');
        });
    }

    public function down(): void
    {
        Schema::table('discovery_runs', function (Blueprint $table) {
            $table->dropColumn('leads_count');
        });
        Schema::dropIfExists('discovery_leads');
    }
};
