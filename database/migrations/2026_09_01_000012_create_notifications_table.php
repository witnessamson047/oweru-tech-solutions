<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->nullable()->constrained('enquiries')->nullOnDelete();
            $table->string('type'); // new_enquiry, stage_reminder, custom
            $table->foreignId('recipient_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('recipient_email')->nullable();
            $table->string('channel')->default('email'); // email, whatsapp, both
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('pending'); // pending, sent, failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index('enquiry_id');
            $table->index('type');
            $table->index('status');
            $table->index('recipient_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
