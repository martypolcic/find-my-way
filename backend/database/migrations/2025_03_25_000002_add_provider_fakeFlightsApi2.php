<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'FakeFlightsApi2',
        ]);

        DB::table('provider_services')->insert([
            'provider_id' => DB::table('providers')->where('name', 'FakeFlightsApi2')->first()->id,
            'service_type' => 'flight',
            'class_name' => 'FakeFlightsApi2',
            'active' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('provider_services')->where('class_name', 'FakeFlightsApi2')->delete();
        DB::table('providers')->where('name', 'FakeFlightsApi2')->delete();
    }
};
