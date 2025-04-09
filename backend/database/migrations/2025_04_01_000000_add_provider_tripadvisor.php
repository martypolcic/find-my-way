<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'Tripadvisor',
        ]);

        DB::table('provider_services')->insert([
            'provider_id' => DB::table('providers')->where('name', 'Tripadvisor')->first()->id,
            'service_type' => 'accomodation',
            'class_name' => 'TripadvisorApi',
            'active' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('provider_services')->where('class_name', 'Tripadvisor')->delete();
        DB::table('providers')->where('name', 'Tripadvisor')->delete();
    }
};
