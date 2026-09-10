<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->where('unit_preference', 'jin')->update(['unit_preference' => 'kg']);
    }

    public function down(): void
    {
        // No reverse conversion for historical jin preference.
    }
};
