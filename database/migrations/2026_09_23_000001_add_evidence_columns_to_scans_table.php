<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            // Component 4 (External API): real-world mobile performance
            // measurements from Google PageSpeed Insights, stored as
            // structured JSON so the dashboard and PDF can read them.
            $table->json('pagespeed')->nullable()->after('band');

            // Component 5 (AI API): interpreted insight (executive summary,
            // prioritised next actions, pitch email) derived from the
            // structured findings. Nullable so legacy scans stay valid.
            $table->json('ai_insight')->nullable()->after('pagespeed');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['pagespeed', 'ai_insight']);
        });
    }
};
