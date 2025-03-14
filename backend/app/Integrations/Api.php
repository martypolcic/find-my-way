<?php

namespace App\Integrations;

use App\Integrations\Params\SearchParams;

interface Api {
    public function searchFlights(SearchParams $searchParams);
    public static function getProvider(): string;
}
