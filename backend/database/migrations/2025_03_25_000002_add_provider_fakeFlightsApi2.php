<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'FakeFlightsApi2',
            'class_name' => 'FakeFlightsApi2',
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('providers')->where('name', 'FakeFlightsApi2')->delete();
    }
};
