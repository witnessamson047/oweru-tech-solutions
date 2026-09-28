<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per "DISCOVER" click / discovery:run execution. Keeps the
        // Overpass query, its raw stats and the python error (if any) so the
        // run can be audited and re-read without hitting Overpass again.
        Schema::create('discovery_runs', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('category')->default('all');
            $table->unsignedInteger('limit')->default(300);
            $table->string('status')->default('running'); // running|completed|failed
            $table->string('source')->default('admin');   // admin|command
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->json('stats')->nullable();   // probe summary counters (verbatim)
            $table->json('result')->nullable();  // websites[] + no_website_sample
            $table->text('error')->nullable();
            $table->unsignedInteger('queued_count')->default(0);
            $table->unsignedInteger('skipped_count')->default(0);
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        // Provenance for auto-queued targets: which discovery run produced
        // them (null = added by hand or harvested from a directory page).
        Schema::table('scrape_targets', function (Blueprint $table) {
            $table->foreignId('discovery_run_id')->nullable()->after('notes')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('scrape_targets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discovery_run_id');
        });
        Schema::dropIfExists('discovery_runs');
    }
};
