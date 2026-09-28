<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scrape_watch_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scraped_business_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // rating_drop | new_review | contact_changed | services_changed | site_down | back_online
            $table->unsignedTinyInteger('severity')->default(1); // 1 = info, 2 = noteworthy, 3 = hot (alerts staff)
            $table->json('changes')->nullable(); // ['field' => ['from' => ..., 'to' => ...]] or event payload
            $table->timestamp('notified_at')->nullable(); // when the staff alert fired (cooldown tracking)
            $table->timestamps();

            $table->index(['scraped_business_id', 'type']);
            $table->index('severity');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scrape_watch_events');
    }
};
