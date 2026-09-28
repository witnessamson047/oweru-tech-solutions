<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scraped_businesses', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->nullable();
            $table->string('website_url')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->text('services')->nullable();
            $table->text('about')->nullable();
            $table->string('source_url');
            $table->string('status')->default('new'); // new | scanned | lead | excluded
            $table->unsignedBigInteger('website_id')->nullable();
            $table->unsignedBigInteger('enquiry_id')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scraped_businesses');
    }
};
