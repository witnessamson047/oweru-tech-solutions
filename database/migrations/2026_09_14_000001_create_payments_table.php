<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('paymentable_type'); // ServicePackage, CarePlan, Scan
            $table->unsignedBigInteger('paymentable_id');
            $table->unsignedBigInteger('enquiry_id')->nullable();
            $table->string('order_tracking_id')->nullable()->index();
            $table->string('merchant_reference')->unique();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('TZS');
            $table->string('description');
            $table->string('status')->default('pending'); // pending, completed, failed, cancelled
            $table->string('pesapal_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['paymentable_type', 'paymentable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
