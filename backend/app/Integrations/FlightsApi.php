<?php

namespace App\Integrations;

use App\Integrations\Params\FlightsSearchParams;

interface FlightsApi {
    public function searchFlights(FlightsSearchParams $searchParams);
    public static function getProvider(): string;
}
