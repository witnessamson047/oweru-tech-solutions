<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('enquiry_id')->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('kind')->default(0)->after('invoice_id'); // 0=direct 1=deposit 2=balance 3=full_settlement
        });

        Schema::table('scraped_businesses', function (Blueprint $table) {
            $table->decimal('rating_avg', 2, 1)->nullable()->after('about');
            $table->unsignedSmallInteger('rating_count')->nullable()->after('rating_avg');
            $table->json('reviews')->nullable()->after('rating_count');
            $table->json('weaknesses')->nullable()->after('reviews');
            $table->timestamp('last_scraped_at')->nullable()->after('weaknesses');
            $table->unsignedSmallInteger('scrape_count')->default(0)->after('last_scraped_at');
            $table->text('last_scrape_error')->nullable()->after('scrape_count');
        });
    }

    public function down(): void
    {
        Schema::table('scraped_businesses', function (Blueprint $table) {
            $table->dropColumn([
                'rating_avg', 'rating_count', 'reviews', 'weaknesses',
                'last_scraped_at', 'scrape_count', 'last_scrape_error',
            ]);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropColumn('kind');
        });
    }
};
