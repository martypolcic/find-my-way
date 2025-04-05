<?php

namespace App\Integrations\AccomodationAPI;

use App\Integrations\AccomodationApi;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Services\AirportService;
use GuzzleHttp\Client as HttpClient;
use DateTimeImmutable;
use App\Models\Provider;
use App\Services\AccomodationService;
use App\Services\AccomodationOfferService;

class FakeAccomodationApi1 implements AccomodationApi
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

    private function fetchAccomodations(string $destinationCity, DateTimeImmutable $checkIn, DateTimeImmutable $checkOut, int $adults, int $children, int $rooms)
    {
        try {
            $response = $this->httpClient->get('accomodation-offers', [
                'query' => [
                    'city' => $destinationCity,
                    'check_in' => $checkIn->format('Y-m-d'),
                    'check_out' => $checkOut->format('Y-m-d'),
                    'adults' => $adults,
                    'children' => $children,
                    'rooms' => $rooms,
                ],
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            // TODO: Log error
            return null;
        }
    }

    public function searchAccomodationOffers(AccomodationsSearchParams $searchParams)
    {
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        $response = $this->fetchAccomodations(
            $destinationAirport->city,
            $searchParams->getCheckInDate(),
            $searchParams->getCheckOutDate(),
            $searchParams->getAdultCount(),
            $searchParams->getChildCount(),
            $searchParams->getRoomCount()
        );
        if (!$response || empty($response['data'])) return;

        $providerId = Provider::where('name', self::getProvider())->first()?->id;

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