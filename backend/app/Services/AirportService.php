<?php

namespace App\Services;

use App\Integrations\OurAirports\OurAirportsApi;
use App\Models\Airport;
use Illuminate\Support\Facades\Cache;

class AirportService
{
    public static function populateAirports(): void
    {
        $ourAirportsApi = new OurAirportsApi();
        $ourAirportsApi->processAirports();
    }
    
    public static function getAirportIdByIata(string $iataCode): int
    {
        $airport = Airport::where('iata_code', $iataCode)->first();
        if ($airport && !$airport->active) {
            $airport->active = true;
            $airport->save();
        }

        return Cache::remember("airport_id_{$iataCode}", 3600, function () use ($iataCode) {
            return Airport::where('iata_code', $iataCode)->firstOrFail()->id;
        });
    }
}
