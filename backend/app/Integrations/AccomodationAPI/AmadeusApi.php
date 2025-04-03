<?php

namespace App\Integrations\AccomodationAPI;

use App\Integrations\AmadeusBaseApi;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Models\Airport;
use App\Models\Provider;
use App\Services\AccomodationOfferService;
use App\Services\AccomodationService;
use App\Integrations\AccomodationApi;
use App\Services\AirportService;

class AmadeusApi extends AmadeusBaseApi implements AccomodationApi
{

    private function searchAccomodations(Airport $destinationAirport)
    {
        $providerId = Provider::where('name', self::getProvider())->first()->id;

        if (AccomodationService::checkIfAccomodationsExist($destinationAirport, $providerId)) return;
        
        $response = $this->makeRequest('GET', 'v1/reference-data/locations/hotels/by-city', [
            'cityCode' => $destinationAirport->iata_code,
            'radius' => 50,
            'radiusUnit' => 'KM',
        ]);
        if (!$response || empty($response['data'])) return;

        foreach ($response['data'] as $accomodation) {
            $accomodationData = [
                'external_id' => $accomodation['hotelId'],
                'name' => $accomodation['name'],
                'airport_id' => $destinationAirport->id,
                'latitude' => $accomodation['geoCode']['latitude'],
                'longitude' => $accomodation['geoCode']['longitude'],
                'provider_id' => $providerId,
                'price_level' => null,
                'description' => null,
            ];

            $accomodation = AccomodationService::createOrUpdateAccomodation($accomodationData);
        }
    }

    public function searchAccomodationOffers(AccomodationsSearchParams $searchParams): void
    {
        $destinationAirport = AirportService::getAirportByIata($searchParams->getAirportIataCode());
        $this->searchAccomodations($destinationAirport);

        //TODO: If one Id is faulty, the whole request fails. We need to handle this.
        $response = $this->makeRequest('GET', 'v3/shopping/hotel-offers', [
            'hotelIds' => implode(',', AccomodationService::getExternalIdList(self::getProvider(), $destinationAirport->id)),
            'checkInDate' => $searchParams->getCheckInDate(),
            'checkOutDate' => $searchParams->getCheckOutDate(),
            'adults' => $searchParams->getAdultCount(),
            'roomQuantity' => $searchParams->getRoomCount(),
            'bestRateOnly' => false,
        ]);
        if (!$response || empty($response['data'])) return;

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
}