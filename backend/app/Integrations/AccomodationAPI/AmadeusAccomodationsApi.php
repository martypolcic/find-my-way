<?php

namespace App\Integrations\AccomodationAPI;

use App\Integrations\AccomodationOffersSearch;
use App\Integrations\AccomodationSearch;
use App\Integrations\AmadeusBaseApi;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Models\Provider;
use App\Services\AccomodationOfferService;
use App\Services\AccomodationService;
use App\Services\AirportService;

class AmadeusAccomodationsApi extends AmadeusBaseApi implements AccomodationOffersSearch, AccomodationSearch
{
    public static function getProvider(): string
    {
        return 'Amadeus';
    }

    public function searchAccomodationOffersAsync(AccomodationsSearchParams $searchParams): \GuzzleHttp\Promise\PromiseInterface
    {
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        
        $promise = $this->makeAsyncRequest('GET', 'v3/shopping/hotel-offers', [
            'hotelIds' => implode(',', AccomodationService::getExternalIdList(self::getProvider(), $destinationAirport->id)),
            'checkInDate' => $searchParams->getCheckInDate(),
            'checkOutDate' => $searchParams->getCheckOutDate(),
            'adults' => $searchParams->getAdultCount(),
            'roomQuantity' => $searchParams->getRoomCount(),
            'bestRateOnly' => false,
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

        $providerId = Provider::where('name', self::getProvider())->first()->id;

        foreach ($response['data'] as $accomodationOffer) {
            foreach ($accomodationOffer['offers'] as $offer) {
                $accomodationOfferData = [
                    'accomodation_id' => AccomodationService::getAccomodationIdByExternalId($accomodationOffer['hotel']['hotelId'], self::getProvider()),
                    'external_id' => $offer['id'],
                    'check_in' => $offer['checkInDate'],
                    'check_out' => $offer['checkOutDate'],
                    'price' => $offer['price']['total'],
                    'currency' => $offer['price']['currency'],
                    'description' => $offer['room']['description']['text'] ?? null,
                ];
                AccomodationOfferService::createOrUpdateAccomodationOffer($accomodationOfferData);
            }
        }
    }

    private function processAccomodationReponse(?array $response, AccomodationsSearchParams $searchParams): void
    {
        if (!$response || empty($response['data'])) return;

        $providerId = Provider::where('name', self::getProvider())->first()->id;
        $airport = AirportService::getAirportByIata($searchParams->getAirportIataCode());

        foreach ($response['data'] as $accomodation) {
            $accomodationData = [
                'external_id' => $accomodation['hotelId'],
                'name' => $accomodation['name'],
                'airport_id' => $airport->id,
                'latitude' => $accomodation['geoCode']['latitude'],
                'longitude' => $accomodation['geoCode']['longitude'],
                'provider_id' => $providerId,
                'price_level' => null,
                'description' => null,
            ];

            AccomodationService::createOrUpdateAccomodation($accomodationData);
        }
    }

    public function searchAccomodationsAsync(AccomodationsSearchParams $searchParams): \GuzzleHttp\Promise\PromiseInterface
    {
        $providerId = Provider::where('name', self::getProvider())->first()->id;
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        
        // TODO : Check if this does what I want it to
        if (AccomodationService::checkIfAccomodationsExist($destinationAirport, $providerId)) {
            return \GuzzleHttp\Promise\Create::promiseFor(null);
        }
        
        $promise = $this->makeAsyncRequest('GET', 'v1/reference-data/locations/hotels/by-city', [
            'cityCode' => $destinationAirport->iata_code,
            'radius' => 50,
            'radiusUnit' => 'KM',
        ]);

        return $promise->then(
            function ($response) use ($searchParams) {
                $data = json_decode($response->getBody()->getContents(), true);
                $this->processAccomodationReponse($data, $searchParams);
            },
            function ($error) {
                // Handle error
                return null;
            }
        );
    }
}