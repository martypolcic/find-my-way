<?php

namespace App\Services;

use App\Models\Accomodation;
use App\Models\AccomodationOffer;

class AccomodationOfferService
{
    public static function createOrUpdateAccomodationOffer(array $accomodationOfferData)
    {
        $latestOffer = Accomodation::where('external_id', $accomodationOfferData['external_id'])
            ->latest()
            ->first();
        if (!$latestOffer || $latestOffer->price_value !== $accomodationOfferData['price_value']) {
            AccomodationOffer::updateOrCreate(
                [
                    'external_id' => $accomodationOfferData['external_id'],
                    'accomodation_id' => $accomodationOfferData['accomodation_id'],
                ],
                [
                    'check_in' => $accomodationOfferData['check_in'],
                    'check_out' => $accomodationOfferData['check_out'],
                    'price' => $accomodationOfferData['price'],
                    'currency' => $accomodationOfferData['currency'],
                    'description' => $accomodationOfferData['description'],
                ]
            );
        }
    }
}
