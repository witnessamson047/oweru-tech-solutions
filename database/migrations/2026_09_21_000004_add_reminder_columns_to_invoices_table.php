<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedTinyInteger('reminders_sent')->default(0)->after('receipt_path');
            $table->timestamp('last_reminder_sent_at')->nullable()->after('reminders_sent');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['reminders_sent', 'last_reminder_sent_at']);
        });
    }
};
