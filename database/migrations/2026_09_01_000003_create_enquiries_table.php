<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();

            // Contact information
            $table->string('name');
            $table->string('business_name');
            $table->string('email');
            $table->string('phone');
            $table->string('country')->default('Tanzania');

            // Project details
            $table->foreignId('package_id')->nullable()->constrained('service_packages')->nullOnDelete();
            $table->string('package_name')->nullable();
            $table->text('problem_description');
            $table->string('current_cost')->nullable();

            // Budget and timing
            $table->string('budget_range');
            $table->date('required_date')->nullable();

            // Pipeline
            $table->enum('stage', [
                'new', 'qualified', 'diagnostic_paid',
                'proposal_sent', 'won', 'lost'
            ])->default('new');
            $table->string('source')->default('website'); // website, scanner, referral, manual
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('last_stage_changed_at')->nullable();

            // Consent
            $table->boolean('consent_given')->default(false);

            $table->timestamps();

            // Indexes
            $table->index('stage');
            $table->index('source');
            $table->index('owner_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
