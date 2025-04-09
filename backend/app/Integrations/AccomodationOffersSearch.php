<?php

namespace App\Integrations;

use App\Integrations\Params\AccomodationsSearchParams;

interface AccomodationOffersSearch {
    public function searchAccomodationOffersAsync(AccomodationsSearchParams $searchParams);
}
 