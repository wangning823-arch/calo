<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['reminder', 'alert', 'achievement', 'system']);
            $table->string('title');
            $table->text('content');
            $table->enum('send_status', ['pending', 'sent', 'failed', 'unauthorized'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
            $table->index('send_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
