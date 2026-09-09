<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('scans')->cascadeOnDelete();
            $table->foreignId('check_id')->nullable()->constrained('scanner_checks')->nullOnDelete();
            $table->string('check_name'); // Denormalized for report generation
            $table->string('area');
            $table->boolean('passed');
            $table->integer('points')->default(0);
            $table->text('evidence')->nullable(); // Technical evidence (raw, deleted after scoring)
            $table->text('finding_text')->nullable(); // Plain-language finding shown to clients
            $table->text('consequence')->nullable(); // Business consequence
            $table->timestamps();

            $table->index('scan_id');
            $table->index('check_id');
            $table->index('passed');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_results');
    }
};
