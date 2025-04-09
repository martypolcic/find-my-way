<?php

namespace App\Integrations\AccomodationAPI;

use App\Integrations\AccomodationOffersSearch;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Services\AirportService;
use GuzzleHttp\Client as HttpClient;
use App\Models\Provider;
use App\Services\AccomodationService;
use App\Services\AccomodationOfferService;

class FakeAccomodationApi2 implements AccomodationOffersSearch
{
    private readonly HttpClient $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'http://fake-accomodations-api-2/api/',
        ]);
    }

    public static function getProvider(): string
    {
        return 'FakeAccomodationApi2';
    }

    public function searchAccomodationOffersAsync(AccomodationsSearchParams $searchParams): \GuzzleHttp\Promise\PromiseInterface
    {
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        
        $promise = $this->httpClient->getAsync('accomodation-offers', [
            'query' => [
                'city' => $destinationAirport->city,
                'check_in' => $searchParams->getCheckInDate()->format('Y-m-d'),
                'check_out' => $searchParams->getCheckOutDate()->format('Y-m-d'),
                'adults' => $searchParams->getAdultCount(),
                'children' => $searchParams->getChildCount(),
                'rooms' => $searchParams->getRoomCount(),
            ],
        ]);

        return $promise->then(
            function ($response) use ($searchParams) {
                $data = json_decode($response->getBody()->getContents(), true);
                $this->processAccomodationOfferResponse($data, $searchParams);
            },
            function ($error) {
                // Handle error
                return [];
            }
        );
    }

    private function processAccomodationOfferResponse(?array $response, AccomodationsSearchParams $searchParams): void
    {
        if (!$response || empty($response['data'])) return;

        $providerId = Provider::where('name', self::getProvider())->first()->id;
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());

        foreach ($response['data'] as $accomodation) {
            $accomodationData = [
                'external_id' => $accomodation['id'],
                'name' => $accomodation['name'],
                'airport_id' => $destinationAirport->id,
                'latitude' => $accomodation['geolocation']['latitude'],
                'longitude' => $accomodation['geolocation']['longitude'],
                'provider_id' => $providerId,
                'price_level' => null,
                'description' => null,
            ];

            $currentAccomodation = AccomodationService::createOrUpdateAccomodation($accomodationData);

            foreach ($accomodation['bookings'] as $offer) {
                $offerData = [
                    'external_id' => $offer['id'],
                    'accomodation_id' => $currentAccomodation->id,
                    'price' => $offer['price']['amount'],
                    'currency' => $offer['price']['currency'],
                    'description' => null,
                    'check_in' => $offer['check_in'],
                    'check_out' => $offer['check_out'],
                ];
                
                AccomodationOfferService::createOrUpdateAccomodationOffer($offerData);
            }
        }
    }
}