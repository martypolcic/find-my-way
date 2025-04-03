<?php

namespace App\Integrations;

use App\Integrations\Params\AccomodationsSearchParams;

interface AccomodationApi {
    public function searchAccomodationOffers(AccomodationsSearchParams $searchParams);
    public static function getProvider(): string;
}
