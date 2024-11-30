<?php

namespace App\Integrations\Ryanair;

use App\Integrations\Api;
use App\Integrations\Params\SearchParams;
use App\Models\Flight;
use Generator;
use GuzzleHttp\Client as HttpClient;

class RyanairApi implements Api {
    private readonly HttpClient $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://www.ryanair.com/api/farfnd/v4/',
        ]);
    }

    // TODO: ryanair results can be paginated, we should handle that
    private function fetchRyanairFlights(string $fromAirport): Generator {
        $today = date("Y-m-d");
        $tomorrow = date("Y-m-d", strtotime("+1 day"));

        $response = $this->httpClient->get('oneWayFares', [
            'query' => [
                'departureAirportIataCode' => $fromAirport,
                'outboundDepartureDateFrom' => $today,
                'outboundDepartureDateTo' => $tomorrow,
                'market' => 'en-gb',
                'adultPaxCount' => 1,
                'outboundDepartureTimeFrom' => '00:00',
                'outboundDepartureTimeTo' => '23:59',
            ],
        ]);

        $decoded = json_decode($response->getBody()->getContents(), true);

        foreach($decoded['fares'] as $flightData) {
            yield $this->transformFlight($flightData);
        }
    }

    private function transformFlight(array $flightData) {
        return new Flight([
            'departureAirportIataCode' => $flightData['outbound']['arrivalAirport']['iataCode'],
        ]);
    }

    public function searchFlights(SearchParams $searchParams): array
    {
        $flights = [];
        
        foreach ($this->fetchRyanairFlights($searchParams->getDepartureAirportIataCode()) as $flight) {
            $flights[] = $flight;
        }

        return $flights;
    }
}