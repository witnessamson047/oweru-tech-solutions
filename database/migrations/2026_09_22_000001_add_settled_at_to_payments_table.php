<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Timestamp set when a completed payment has been applied to its
        // invoice (status transitions + receipt). Guards against the callback,
        // IPN and reconciliation paths settling the same payment twice.
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('settled_at')->nullable()->after('paid_at');
            $table->index(['status', 'invoice_id', 'settled_at']);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status', 'invoice_id', 'settled_at']);
            $table->dropColumn('settled_at');
        });
    }
};
