<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->foreignId('exercise_type_id')->constrained('exercise_types');
            $table->integer('duration_minutes');
            $table->enum('intensity', ['light', 'moderate', 'heavy']);
            $table->decimal('estimated_calories', 7, 1);
            $table->decimal('distance_km', 6, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_records');
    }
};
