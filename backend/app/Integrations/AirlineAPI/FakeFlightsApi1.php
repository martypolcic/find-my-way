<?php

namespace App\Integrations\AirlineAPI;

use App\Integrations\FlightsApi;
use App\Integrations\Params\SearchParams;
use App\Models\Provider;
use App\Services\AirportService;
use App\Services\FlightService;
use App\Services\FlightPriceService;
use GuzzleHttp\Client as HttpClient;

class FakeFlightsApi1 implements FlightsApi {
    private readonly HttpClient $httpClient;

    public function __construct() 
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'http://fake-flights-api-1/api/',
        ]);
    }

    public static function getProvider(): string 
    {
        return 'FakeFlightsApi1';
    }

    private function fetchDestinations(SearchParams $searchParams)
    {
        $departureDate = $searchParams->getDepartureDate()->format('Y-m-d');

        try {
            $response = $this->httpClient->get('flights', [
                'query' => [
                    'departureAirportIataCode' => $searchParams->getDepartureAirportIataCode(),
                    'departureDate' => $departureDate,
                    'outboundDepartureDateTo' => $departureDate,
                    'passengerCount' => $searchParams->getPassengerCount(),
                ],
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // TODO: Log error
            return null;
        }
    }

    public function searchFlights(SearchParams $search_params)
    {
        $response = $this->fetchDestinations($search_params);
        if (!$response || empty($response['data'])) return;

        $departureAirportId = AirportService::getAirportIdByIata($search_params->getDepartureAirportIataCode());
        $providerId = Provider::where('name', self::getProvider())->first()?->id;

        foreach ($response['data'] as $fare) {
            $flightData = [
                'flight_number' => null,
                'flight_key' => 'FAKE_FLIGHTS_API_1~' . $fare['route'] . '~' . $fare['departure'] . '~' . $fare['arrival'],
                'departure_date' => $fare['departure'],
                'arrival_date' => $fare['arrival'],
                'departure_airport_id' => $departureAirportId,
                'arrival_airport_id' => AirportService::getAirportIdByIata(explode(' - ', $fare['route'])[1]),
                'price_value' => $fare['price']['amount'],
                'currency_code' => $fare['price']['currency'],
                'airline_id' => null,
                'provider_id' => $providerId,
            ];

            $flight = FlightService::createOrUpdateFlight($flightData);
            FlightPriceService::storeFlightPrice($flight->id, $fare['price']['amount'], $fare['price']['currency']);
        }
    }
}
