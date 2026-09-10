<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone', 20)->unique();
            $table->string('password');
            $table->rememberToken();

            // Profile fields
            $table->enum('gender', ['male', 'female']);
            $table->date('date_of_birth');
            $table->decimal('height', 5, 1)->nullable();
            $table->enum('activity_level', ['sedentary', 'light', 'moderate', 'heavy'])->default('sedentary');
            $table->enum('special_group', ['none', 'pregnant', 'lactating'])->default('none');
            $table->enum('unit_preference', ['jin', 'kg'])->default('kg');

            // Agreement & cancellation
            $table->timestamp('agreed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('cancellation_deadline')->nullable();

            // Password reset (phone-based)
            $table->string('password_reset_token')->nullable();
            $table->timestamp('password_reset_expires_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('phone', 20)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
