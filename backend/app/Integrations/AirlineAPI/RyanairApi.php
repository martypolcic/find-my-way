<?php

namespace App\Integrations\AirlineAPI;

use App\Integrations\Api;
use App\Integrations\Params\SearchParams;
use App\Models\Provider;
use App\Services\AirportService;
use App\Services\FlightService;
use App\Services\FlightPriceService;
use GuzzleHttp\Client as HttpClient;

class RyanairApi implements Api {
    private readonly HttpClient $httpClient;

    public function __construct() 
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://www.ryanair.com/api/farfnd/v4/',
        ]);
    }

    public static function getProvider(): string 
    {
        return 'Ryanair';
    }

    private function fetchDestinations(SearchParams $searchParams)
    {
        $departureDate = $searchParams->getDepartureDate()->format('Y-m-d');

        try {
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

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // TODO: Log error
            return null;
        }
    }

    public function searchFlights(SearchParams $searchParams)
    {
        $response = $this->fetchDestinations($searchParams);
        if (!$response || empty($response['fares'])) return;

        $departureAirportId = AirportService::getAirportIdByIata($searchParams->getDepartureAirportIataCode());
        $airlineId = null; //TODO: Implement
        $providerId = Provider::where('name', self::getProvider())->first()->id;

        foreach ($response['fares'] as $fare) {
            $flightData = [
                'flight_number' => $fare['outbound']['flightNumber'],
                'flight_key' => $fare['outbound']['flightKey'],
                'departure_date' => $fare['outbound']['departureDate'],
                'arrival_date' => $fare['outbound']['arrivalDate'],
                'departure_airport_id' => $departureAirportId,
                'arrival_airport_id' => AirportService::getAirportIdByIata($fare['outbound']['arrivalAirport']['iataCode']),
                'price_value' => $fare['outbound']['price']['value'],
                'currency_code' => $fare['outbound']['price']['currencyCode'],
                'airline_id' => $airlineId,
                'provider_id' => $providerId,
            ];
            
            $flight = FlightService::createOrUpdateFlight($flightData);
            FlightPriceService::storeFlightPrice($flight->id, $fare['outbound']['price']['value'], $fare['outbound']['price']['currencyCode']);
        }
    }
}
