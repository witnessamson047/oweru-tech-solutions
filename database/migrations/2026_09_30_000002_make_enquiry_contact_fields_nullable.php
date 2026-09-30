<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Contact page support: light "just a question" enquiries.
 * business_name and budget_range become optional, and 'contact'
 * joins 'website' as a recognised enquiry source.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('business_name')->nullable()->change();
            $table->string('budget_range')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Backfill so the columns can be made NOT NULL again safely.
        DB::table('enquiries')->whereNull('business_name')->update(['business_name' => 'Not provided']);
        DB::table('enquiries')->whereNull('budget_range')->update(['budget_range' => 'prefer_not_say']);

        Schema::table('enquiries', function (Blueprint $table) {
            $table->string('business_name')->nullable(false)->change();
            $table->string('budget_range')->nullable(false)->change();
        });
    }
};
