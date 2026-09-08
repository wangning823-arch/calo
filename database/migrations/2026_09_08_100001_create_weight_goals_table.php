<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weight_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('mode', ['lose', 'maintain'])->default('lose');
            $table->decimal('start_weight', 6, 2);
            $table->decimal('target_weight', 6, 2);
            $table->date('target_date');
            $table->decimal('daily_calorie_budget', 7, 1);
            $table->decimal('target_deficit', 6, 1)->default(500);
            $table->enum('status', ['active', 'completed', 'abandoned'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weight_goals');
    }
};
