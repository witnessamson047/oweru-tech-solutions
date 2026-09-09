<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recommendations', function (Blueprint $table) {
            $table->id();
            $table->string('check_name');
            $table->string('area');
            $table->text('finding_example')->nullable();
            $table->text('consequence')->nullable();
            $table->string('solution');
            $table->string('service_type'); // Maps to Oweru service category
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index('area');
            $table->index('active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recommendations');
    }
};
