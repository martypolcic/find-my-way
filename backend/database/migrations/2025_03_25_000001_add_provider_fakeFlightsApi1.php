<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'FakeFlightsApi1',
        ]);

        DB::table('provider_services')->insert([
            'provider_id' => DB::table('providers')->where('name', 'FakeFlightsApi1')->first()->id,
            'service_type' => 'flight',
            'class_name' => 'FakeFlightsApi1',
            'active' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('provider_services')->where('class_name', 'FakeFlightsApi1')->delete();
        DB::table('providers')->where('name', 'FakeFlightsApi1')->delete();
    }
};
