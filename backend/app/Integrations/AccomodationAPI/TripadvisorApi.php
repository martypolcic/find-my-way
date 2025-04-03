<?php

namespace App\Integrations\AccomodationAPI;

use App\Models\Airport;
use App\Models\Provider;
use App\Services\AccomodationService;
use App\Integrations\AccomodationApi;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Services\AirportService;
use GuzzleHttp\Client as HttpClient;

class TripadvisorApi implements AccomodationApi
{
    private readonly HttpClient $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'https://api.content.tripadvisor.com/api/v1/location/',
        ]);
    }

    public static function getProvider(): string
    {
        return 'Tripadvisor';
    }

    private function fetchAccomodations(string $destinationCity)
    {
        try {
            $response = $this->httpClient->get('search', [
                'query' => [
                    'key' => config('services.tripadvisor.api_key'),
                    'searchQuery' => $destinationCity,
                    'category' => 'hotels',
                    'radius' => 50,
                    'radiusUnit' => 'km',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // TODO: Log error
            return null;
        }
    }

    private function fetchAccomodationDetails(string $locationId)
    {
        try {
            $response = $this->httpClient->get($locationId . '/details', [
                'query' => [
                    'key' => config('services.tripadvisor.api_key'),
                    'language' => 'en',
                    'currency' => 'EUR',
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // TODO: Log error
            return null;
        }
    }

    //TODO: Think about parameter and universal solution to this method
    private function searchAccomodations(Airport $destinationAirport)
    {
        $providerId = Provider::where('name', self::getProvider())->first()->id;
        if (AccomodationService::checkIfAccomodationsExist($destinationAirport, $providerId)) return;
        
        $response = $this->fetchAccomodations($destinationAirport->city);
        if (!$response || empty($response['data'])) return;

        foreach ($response['data'] as $accomodation) {
            $response = $this->fetchAccomodationDetails($accomodation['location_id']);
            if (!$response || empty($response)) continue;

            $accomodationData = [
                'external_id' => $response['location_id'],
                'name' => $response['name'],
                'airport_id' => $destinationAirport->id,
                'latitude' => $response['latitude'],
                'longitude' => $response['longitude'],
                'provider_id' => $providerId,
                'price_level' => $response['price_level'] ?? null,
                'description' => $response['description'] ?? null,
            ];

            AccomodationService::createOrUpdateAccomodation($accomodationData);
        }
    }

    public function searchAccomodationOffers(AccomodationsSearchParams $searchParams)
    {
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        $this->searchAccomodations($destinationAirport);

        //Tripadvisor does not provide a way to search for accomodation offers
    }
}