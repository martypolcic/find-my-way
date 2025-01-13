<?php

namespace App\Integrations\Ryanair;

use App\Integrations\Api;
use App\Integrations\Params\SearchParams;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\FlightPrice;
use Generator;
use GuzzleHttp\Client as HttpClient;
use DateTimeImmutable;

class RyanairApi implements Api {
    private readonly HttpClient $httpClient;

    public function __construct() {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://www.ryanair.com/api/farfnd/v4/',
        ]);
    }

    // TODO: ryanair results can be paginated, we should handle that
    private function fetchRyanairFlights(SearchParams $searchParams): Generator {
        $departureDate = $searchParams->getDepartureDate()->format('Y-m-d');

        $response = $this->httpClient->get('oneWayFares', [
            'query' => [
                'departureAirportIataCode' => $searchParams->getDepartureAirportIataCode(),
                'outboundDepartureDateFrom' => $departureDate,
                'outboundDepartureDateTo' => $departureDate,
                'market' => 'en-gb',
                'adultPaxCount' => $searchParams->getPassengerCount(),
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
        $flight = new Flight();
        $flightPrice = new FlightPrice();

        $flight->flight_number = $flightData['outbound']['flightNumber'];
        $flight->flight_key = $flightData['outbound']['flightKey'];
        $flight->departure_date = new DateTimeImmutable($flightData['outbound']['departureDate']);
        $flight->arrival_date = new DateTimeImmutable($flightData['outbound']['arrivalDate']);
        $flight->departure_airport_id = Airport::where('iata_code', $flightData['outbound']['departureAirport']['iataCode'])->first()->id;
        $flight->arrival_airport_id = Airport::where('iata_code', $flightData['outbound']['arrivalAirport']['iataCode'])->first()->id;
        // TODO: change where to something more efficient

        $flightPrice->price_value = $flightData['outbound']['price']['value'];
        $flightPrice->currency_code = $flightData['outbound']['price']['currencyCode'];

        $flight->flightPrices = $flightPrice;

        return $flight;
    }

    public function searchFlights(SearchParams $searchParams): array
    {
        $flights = [];
        
        foreach ($this->fetchRyanairFlights($searchParams) as $flight) {
            $flights[] = $flight;
        }

        return $flights;
    }
}