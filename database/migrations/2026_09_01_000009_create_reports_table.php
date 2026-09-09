<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_id')->constrained('scans')->cascadeOnDelete();
            $table->string('file_path');
            $table->timestamp('generated_at')->nullable();
            $table->integer('downloads')->default(0);
            $table->timestamps();

            $table->index('scan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
