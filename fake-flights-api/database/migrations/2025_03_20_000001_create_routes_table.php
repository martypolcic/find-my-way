<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('departure_airport', 3);
            $table->string('arrival_airport', 3);
            $table->integer('duration'); // Flight duration in minutes
            $table->timestamps();
        
            $table->foreign('departure_airport')->references('iata_code')->on('airports')->onDelete('cascade');
            $table->foreign('arrival_airport')->references('iata_code')->on('airports')->onDelete('cascade');
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('routes');
    }
};

