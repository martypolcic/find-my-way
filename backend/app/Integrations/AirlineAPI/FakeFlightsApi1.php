<?php

namespace App\Integrations\AirlineAPI;

use App\Integrations\FlightsApi;
use App\Integrations\Params\FlightsSearchParams;
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
    
    public function searchFlightsAsync(FlightsSearchParams $search_params): \GuzzleHttp\Promise\PromiseInterface
    {
        $departureDate = $search_params->getDepartureDate()->format('Y-m-d');
        
        $promise = $this->httpClient->getAsync('flights', [
            'query' => [
                'departureAirportIataCode' => $search_params->getDepartureAirportIataCode(),
                'departureDate' => $departureDate,
                'outboundDepartureDateTo' => $departureDate,
                'passengerCount' => $search_params->getAdultCount(),
            ],
        ]);
        
        return $promise->then(
            function ($response) use ($search_params) {
                $data = json_decode($response->getBody()->getContents(), true);
                $this->processFlightResponse($data, $search_params);
            },
            function ($exception) {
                // Log error
                return null;
            }
        );
    }

    private function processFlightResponse(?array $response, FlightsSearchParams $search_params)
    {
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
