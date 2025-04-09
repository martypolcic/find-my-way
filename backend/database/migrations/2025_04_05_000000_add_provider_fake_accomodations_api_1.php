<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('providers')->insert([
            'name' => 'FakeAccomodationApi1',
        ]);

        DB::table('provider_services')->insert([
            'provider_id' => DB::table('providers')->where('name', 'FakeAccomodationApi1')->first()->id,
            'service_type' => 'accomodation',
            'class_name' => 'FakeAccomodationApi1',
            'active' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('provider_services')->where('class_name', 'FakeAccomodationApi1')->delete();
        DB::table('providers')->where('name', 'FakeAccomodationApi1')->delete();
    }
};
