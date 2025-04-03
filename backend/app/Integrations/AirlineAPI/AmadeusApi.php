<?php

namespace App\Integrations\AirlineAPI;

use App\Integrations\FlightsApi;
use App\Integrations\AmadeusBaseApi;
use App\Integrations\Params\FlightsSearchParams;
use App\Services\AirportService;
use App\Models\Airline;
use App\Models\Provider;
use App\Services\AirlineService;
use App\Services\FlightService;
use App\Services\FlightPriceService;

class AmadeusApi extends AmadeusBaseApi implements FlightsApi {

    public function searchFlights(FlightsSearchParams $searchParams)
    {
        $departureDate = $searchParams->getDepartureDate()->format('Y-m-d');

        $response = $this->makeRequest('GET', 'v1/shopping/flight-destinations', [
            'origin' => $searchParams->getDepartureAirportIataCode(),
            'departureDate' => $departureDate,
            'oneWay' => 'true',
            'nonStop' => 'false',
        ]);
        if (!$response || empty($response['data'])) return;

        $departureAirportId = AirportService::getAirportIdByIata($searchParams->getDepartureAirportIataCode());
        $providerId = Provider::where('name', self::getProvider())->first()->id;

        foreach ($response['data'] as $destination) {
            $flightData = [
                'flight_number' => null,
                'flight_key' => 'AMADEUS~' . $destination['origin'] . '~' . $destination['destination'] . '~' . $destination['departureDate'],
                'departure_date' => $destination['departureDate'],
                'arrival_date' => null,
                'departure_airport_id' => $departureAirportId,
                'arrival_airport_id' => AirportService::getAirportIdByIata($destination['destination']),
                'price_value' => $destination['price']['total'],
                'currency_code' => 'EUR',
                'airline_id' => null,
                'provider_id' => $providerId,
            ];

            $flight = FlightService::createOrUpdateFlight($flightData);
            FlightPriceService::storeFlightPrice($flight->id, $destination['price']['total'], 'EUR');
        }
    }

    //TODO: Implement this method
    public function searchFlightsOnRoute()
    {

    }

    //Implemented in searchFlightsOnRoute
    private function getAirlineId(string $airlineCode)
    {
        $airline = Airline::where('iata_code', $airlineCode)->firstOrNull();
        if ($airline) return $airline->id;

        $response = $this->makeRequest('GET', 'v1/reference-data/airlines', [
            'airlineCodes' => $airlineCode,
        ]);
        if (!$response || empty($response['data'])) return null;

        $airlineData = [
            'name' => $response['data'][0]['businessName'],
            'iata_code' => $response['data'][0]['iataCode'],
            'icao_code' => $response['data'][0]['icaoCode'],
        ];

        return AirlineService::createAirline($airlineData)->id;
    }
}