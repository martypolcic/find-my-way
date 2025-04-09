<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'Ryanair',
        ]);

        DB::table('provider_services')->insert([
            'provider_id' => DB::table('providers')->where('name', 'Ryanair')->first()->id,
            'service_type' => 'flight',
            'class_name' => 'RyanairFlightsApi',
            'active' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('provider_services')->where('class_name', 'RyanairFlightsApi')->delete();
        DB::table('providers')->where('name', 'Ryanair')->delete();
    }
};
