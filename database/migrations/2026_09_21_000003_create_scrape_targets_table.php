<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_targets', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->unsignedTinyInteger('status')->default(1); // 0=paused 1=active 2=done
            $table->timestamp('last_scraped_at')->nullable();
            $table->unsignedSmallInteger('scrape_count')->default(0);
            $table->text('last_error')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'last_scraped_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_targets');
    }
};
