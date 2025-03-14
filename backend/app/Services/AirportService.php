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
}
