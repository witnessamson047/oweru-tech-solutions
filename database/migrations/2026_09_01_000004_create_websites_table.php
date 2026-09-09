<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('websites', function (Blueprint $table) {
            $table->id();
            $table->string('business_name');
            $table->string('url');
            $table->string('sector')->nullable();
            $table->string('status')->default('active');
            $table->string('exclusion_status')->default('active'); // active, excluded
            $table->text('exclusion_reason')->nullable();
            $table->foreignId('enquiry_id')->nullable()->constrained('enquiries')->nullOnDelete();
            $table->timestamps();

            $table->unique('url');
            $table->index('status');
            $table->index('sector');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('websites');
    }
};
