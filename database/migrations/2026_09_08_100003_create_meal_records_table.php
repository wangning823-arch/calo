<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->string('timezone')->default('Asia/Shanghai');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->foreignId('food_id')->constrained('food_items');
            $table->integer('food_version')->default(1);
            $table->decimal('serving_grams', 7, 1);
            $table->decimal('calculated_calories', 7, 1);
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_records');
    }
};
