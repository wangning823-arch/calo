<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('aliases')->nullable();
            $table->string('category');
            $table->decimal('calories_per_100g', 7, 1);
            $table->decimal('protein_per_100g', 6, 1);
            $table->decimal('carbs_per_100g', 6, 1);
            $table->decimal('fat_per_100g', 6, 1);
            $table->decimal('serving_size', 7, 1)->default(100);
            $table->string('serving_unit')->default('g');
            $table->enum('source', ['crawled', 'official', 'user_custom'])->default('crawled');
            $table->enum('review_status', ['pending', 'approved', 'rejected'])->default('approved');
            $table->boolean('is_user_custom')->default(false);
            $table->integer('version')->default(1);
            $table->string('source_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index('category');
            $table->index('source');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
