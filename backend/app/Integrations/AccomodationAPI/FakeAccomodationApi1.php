<?php

namespace App\Integrations\AccomodationAPI;

use App\Integrations\AccomodationOffersSearch;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Services\AirportService;
use GuzzleHttp\Client as HttpClient;
use App\Models\Provider;
use App\Services\AccomodationService;
use App\Services\AccomodationOfferService;

class FakeAccomodationApi1 implements AccomodationOffersSearch
{
    private readonly HttpClient $httpClient;

    public function __construct()
    {
        $this->httpClient = new HttpClient([
            'base_uri' => 'http://fake-accomodations-api-1/api/',
        ]);
    }

    public static function getProvider(): string
    {
        return 'FakeAccomodationApi1';
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
                return null;
            }
        );
    }

    private function processAccomodationOfferResponse(?array $response, AccomodationsSearchParams $searchParams): void
    {
        if (!$response || empty($response['data'])) return;

        $providerId = Provider::where('name', self::getProvider())->first()?->id;
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());

        foreach ($response['data'] as $accomodation) {
            $accomodationData = [
                'external_id' => $accomodation['id'],
                'name' => $accomodation['title'],
                'airport_id' => $destinationAirport->id,
                'latitude' => $accomodation['latitude'],
                'longitude' => $accomodation['longitude'],
                'provider_id' => $providerId,
                'price_level' => null,
                'description' => null,
            ];

            $currentAccomodation = AccomodationService::createOrUpdateAccomodation($accomodationData);

            foreach ($accomodation['offers'] as $offer) {
                $price = explode(' ', $offer['price']);

                $offerData = [
                    'external_id' => $offer['offer_id'],
                    'accomodation_id' => $currentAccomodation->id,
                    'price' => $price[0],
                    'currency' => $price[1],
                    'description' => null,
                    'check_in' => $offer['booking_start_date'],
                    'check_out' => $offer['booking_end_date'],
                ];
                
                AccomodationOfferService::createOrUpdateAccomodationOffer($offerData);
            }
        }
    }
}