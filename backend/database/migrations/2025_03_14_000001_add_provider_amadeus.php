<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'Amadeus',
        ]);

        DB::table('provider_services')->insert([
            'provider_id' => DB::table('providers')->where('name', 'Amadeus')->first()->id,
            'service_type' => 'flight',
            'class_name' => 'AmadeusFlightsApi',
            'active' => true,
        ]);

        DB::table('provider_services')->insert([
            'provider_id' => DB::table('providers')->where('name', 'Amadeus')->first()->id,
            'service_type' => 'accomodation',
            'class_name' => 'AmadeusAccomodationsApi',
            'active' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('provider_services')->where('provider_id', DB::table('providers')->where('name', 'Amadeus')->first()->id)->delete();
        DB::table('providers')->where('name', 'Amadeus')->delete();
    }
};
