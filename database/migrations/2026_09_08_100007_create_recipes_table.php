<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('meal_type', ['breakfast', 'lunch', 'dinner', 'snack']);
            $table->decimal('total_calories', 7, 1);
            $table->decimal('protein', 7, 1);
            $table->decimal('carbs', 7, 1);
            $table->decimal('fat', 7, 1);
            $table->json('ingredients');
            $table->json('steps');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('meal_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
