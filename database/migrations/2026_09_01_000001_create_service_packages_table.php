<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('group', ['individuals', 'sme', 'corporate']);
            $table->text('description')->nullable();
            $table->decimal('price_tzs', 12, 2)->default(0);
            $table->decimal('price_usd', 10, 2)->default(0);
            $table->integer('delivery_days')->default(7);
            $table->boolean('is_featured')->default(false);
            $table->boolean('active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};
