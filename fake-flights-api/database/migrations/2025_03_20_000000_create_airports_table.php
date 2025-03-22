<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('airports', function (Blueprint $table) {
            $table->id();
            $table->string('iata_code', 3)->unique();
            $table->string('icao_code', 4)->unique();
            $table->string('name');
            $table->string('city');
            $table->string('country');
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('airports');
    }
};

