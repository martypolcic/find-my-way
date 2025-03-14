<?php

namespace App\Services;

use App\Models\Flight;

class FlightService
{
    /***
     * Create or update a flight record.
     * 
     * @param array $flightData
     * @return Flight
     */
    public static function createOrUpdateFlight(array $flightData): Flight
    {
        $flight = Flight::firstOrNew([
            'flight_key' => $flightData['flight_key']
        ]);

        $flight->fill([
            'flight_number' => $flightData['flight_number'],
            'departure_date' => $flightData['departure_date'],
            'arrival_date' => $flightData['arrival_date'],
            'departure_airport_id' => $flightData['departure_airport_id'],
            'arrival_airport_id' => $flightData['arrival_airport_id'],
            'airline_id' => $flightData['airline_id'],
            'provider_id' => $flightData['provider_id'],
        ]);

        $flight->save();

        return $flight;
    }
}
