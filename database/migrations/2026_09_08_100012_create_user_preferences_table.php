<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();

            // Notification toggles
            $table->boolean('notification_breakfast')->default(true);
            $table->boolean('notification_lunch')->default(true);
            $table->boolean('notification_dinner')->default(true);
            $table->boolean('notification_weigh_in')->default(true);
            $table->boolean('notification_health_alert')->default(true);
            $table->boolean('notification_achievement')->default(true);

            // Notification times
            $table->time('breakfast_time')->default('08:00');
            $table->time('lunch_time')->default('12:00');
            $table->time('dinner_time')->default('18:00');
            $table->time('weigh_in_time')->default('21:00');

            // Preferences
            $table->enum('theme', ['system', 'light', 'dark'])->default('system');
            $table->boolean('onboarding_completed')->default(false);
            $table->boolean('exercise_refill_enabled')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
