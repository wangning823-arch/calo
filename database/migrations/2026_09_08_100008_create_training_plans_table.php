<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('training_plans', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->enum('goal', ['lose', 'shape']);
            $table->enum('difficulty', ['beginner', 'advanced']);
            $table->integer('duration_weeks');
            $table->json('exercises');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('goal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('training_plans');
    }
};
