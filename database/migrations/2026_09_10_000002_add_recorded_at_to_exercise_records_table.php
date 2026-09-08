<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercise_records', function (Blueprint $table) {
            $table->dateTime('recorded_at')->nullable()->after('date');
        });

        // Backfill recorded_at from existing date column (set to noon as neutral default)
        DB::table('exercise_records')
            ->whereNull('recorded_at')
            ->update(['recorded_at' => DB::raw("CONCAT(date, ' 12:00:00')")]);
    }

    public function down(): void
    {
        Schema::table('exercise_records', function (Blueprint $table) {
            $table->dropColumn('recorded_at');
        });
    }
};
