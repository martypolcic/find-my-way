<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'Amadeus',
            'class_name' => 'AmadeusApi',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('providers')->where('name', 'Amadeus')->delete();
    }
};
