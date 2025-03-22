<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('flights', function (Blueprint $table) {
            $table->id();
            $table->string('departure_airport', 3);
            $table->string('arrival_airport', 3);
            $table->dateTime('departure_date');
            $table->dateTime('arrival_date');
            $table->decimal('price', 8, 2);
            $table->string('currency');
            $table->timestamps();

            $table->foreign('departure_airport')->references('iata_code')->on('airports')->onDelete('cascade');
            $table->foreign('arrival_airport')->references('iata_code')->on('airports')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('flights');
    }
};

