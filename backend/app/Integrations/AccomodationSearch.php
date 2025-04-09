<?php
namespace App\Integrations;

use App\Integrations\Params\AccomodationsSearchParams;

interface AccomodationSearch
{
    public function searchAccomodationsAsync(AccomodationsSearchParams $searchParams);
}