<?php

namespace App\Services;

use App\Models\Airline;

class AirlineService
{
    public static function createAirline($airlineData) {
        $airline = new Airline();
        $airline->name = $airlineData['name'];
        $airline->iata_code = $airlineData['iataCode'];
        $airline->icao_code = $airlineData['icaoCode'];
        $airline->save();
        return $airline;
    }

    public static function getAirlineIdByIata($iataCode) {
        $airline = Airline::where('iata_code', $iataCode)->first();
        return $airline ? $airline->id : null;
    }
}
