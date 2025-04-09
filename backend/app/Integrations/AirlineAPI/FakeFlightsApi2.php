<?php

namespace App\Integrations\AirlineAPI;

use App\Integrations\FlightsApi;
use App\Integrations\Params\FlightsSearchParams;
use App\Models\Provider;
use App\Services\AirportService;
use App\Services\FlightService;
use App\Services\FlightPriceService;
use GuzzleHttp\Client as HttpClient;

class FakeFlightsApi2 implements FlightsApi {
    private readonly HttpClient $httpClient;

    public function __construct() 
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'http://fake-flights-api-2/api/',
        ]);
    }

    public static function getProvider(): string 
    {
        return 'FakeFlightsApi2';
    }
    
    private function processFlightResponse(?array $response, FlightsSearchParams $searchParams): void
    {
        if (!$response || empty($response['data'])) return;

        $departureAirportId = AirportService::getAirportIdByIata($searchParams->getDepartureAirportIataCode());
        $providerId = Provider::where('name', self::getProvider())->first()?->id;

        foreach ($response['data'] as $fare) {
            $price = explode(' ', $fare['cost']);

            $flightData = [
                'flight_number' => null,
                'flight_key' => 'FAKE_FLIGHTS_API_2~' . $fare['origin'] . '~' . $fare['destination'] . '~' . $fare['schedule']['departure_at'] . '~' . $fare['schedule']['arrival_at'],
                'departure_date' => $fare['schedule']['departure_at'],
                'arrival_date' => $fare['schedule']['arrival_at'],
                'departure_airport_id' => $departureAirportId,
                'arrival_airport_id' => AirportService::getAirportIdByIata($fare['destination']),
                'price_value' => $price[0],
                'currency_code' => $price[1],
                'airline_id' => null,
                'provider_id' => $providerId,
            ];

            $flight = FlightService::createOrUpdateFlight($flightData);
            FlightPriceService::storeFlightPrice($flight->id, $price[0], $price[1]);
        }
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
}
