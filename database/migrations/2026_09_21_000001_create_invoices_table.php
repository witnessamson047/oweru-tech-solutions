<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique(); // OWU-INV-2026-0001
            $table->foreignId('enquiry_id')->constrained()->cascadeOnDelete();
            $table->string('title'); // e.g. "Website Redesign & Care Plan"
            $table->text('description')->nullable();
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('TZS');
            $table->unsignedTinyInteger('deposit_percent')->default(50); // 50 = 50% upfront
            $table->decimal('deposit_due', 12, 2);
            $table->unsignedTinyInteger('status')->default(0); // 0=draft 1=issued 2=part_paid 3=paid 4=cancelled
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('due_at')->nullable(); // deposit due date
            $table->timestamp('receipt_generated_at')->nullable();
            $table->string('receipt_path')->nullable();
            $table->json('meta')->nullable(); // service breakdown snapshot
            $table->timestamps();

            $table->index(['enquiry_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
