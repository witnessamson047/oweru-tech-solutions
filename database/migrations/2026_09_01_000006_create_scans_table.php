<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('website_id')->nullable()->constrained('websites')->nullOnDelete();
            $table->string('url');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->integer('score')->nullable();
            $table->string('band')->nullable(); // Critical, Weak, Adequate, Strong
            $table->string('source')->default('manual'); // public, manual, batch
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('website_id');
            $table->index('status');
            $table->index('score');
            $table->index('band');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
