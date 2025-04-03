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

    public static function getDestinationIds(int $departureAirportId, string $departureDate): array
    {
        return Flight::where('departure_airport_id', $departureAirportId)
            ->where('departure_date', $departureDate)
            ->pluck('arrival_airport_id')
            ->unique()
            ->toArray();
    }

    public static function getDepartureFlights(int $departureAirportId, string $departureDate): array
    {
        return Flight::where('departure_airport_id', $departureAirportId)
            ->whereDate('departure_date', $departureDate)
            ->get();
    }
}
