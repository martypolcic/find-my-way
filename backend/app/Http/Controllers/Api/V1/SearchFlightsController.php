<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchFlightsRequest;
use App\Http\Resources\V1\FlightCollection;
use App\Integrations\Params\SearchParams;
use App\Integrations\Ryanair\RyanairApi;

class SearchFlightsController extends Controller
{
    public function index(SearchFlightsRequest $request, RyanairApi $ryanairApi)
    {
        $validated = $request->validated();

        $searchParams = SearchParams::fromArray($validated);
        
        return new FlightCollection($ryanairApi->searchFlights($searchParams));
    }
}
