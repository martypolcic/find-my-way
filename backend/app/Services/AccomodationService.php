<?php

namespace App\Services;

use App\Models\Airport;
use App\Models\Accomodation;
use App\Models\Provider;

class AccomodationService
{
    public static function checkIfAccomodationsExist(Airport $airport, int $providerId): bool
    {
        return $airport->accomodations()
            ->where('provider_id', $providerId)
            ->exists();
    }

    public static function createOrUpdateAccomodation(array $accomodationData)
    {
        return Accomodation::updateOrCreate(
            ['external_id' => $accomodationData['external_id']],
            $accomodationData
        );
    }

    public static function getExternalIdList(string $provider, int $airportId)
    {
        $providerId = Provider::where('name', $provider)->first()->id;
        return Accomodation::where('provider_id', $providerId)
            ->where('airport_id', $airportId)
            ->pluck('external_id')
            ->toArray();
    }

    public static function getAccomodationIdByExternalId(string $externalId, string $providerName)
    {
        $providerId = Provider::where('name', $providerName)->first()->id;

        return Accomodation::where('external_id', $externalId)
            ->where('provider_id', $providerId)
            ->first()
            ?->id;
    }
}
