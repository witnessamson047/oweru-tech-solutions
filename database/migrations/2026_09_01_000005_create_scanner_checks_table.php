<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanner_checks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('area'); // Security, Mobile, Speed, Function, Findability, Trust, Commerce, Freshness
            $table->integer('weight')->default(1); // Points awarded when passed
            $table->boolean('enabled')->default(true);
            $table->text('description')->nullable();
            $table->text('wording_pass')->nullable(); // Wording shown to client when passed
            $table->text('wording_fail')->nullable(); // Wording shown to client when failed
            $table->timestamps();

            $table->index('area');
            $table->index('enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scanner_checks');
    }
};
