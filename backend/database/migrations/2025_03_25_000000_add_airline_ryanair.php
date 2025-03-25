<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('airlines')->insert([
            'name' => 'Ryanair',
            'iata_code' => 'FR',
            'icao_code' => 'RYR',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('airlines')->where('iata_code', 'FR')->delete();
    }
};
