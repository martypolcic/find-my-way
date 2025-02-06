<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchFlightsRequest;
use App\Http\Resources\V1\FlightCollection;
use App\Integrations\Params\SearchParams;
use App\Integrations\Ryanair\RyanairApi;
use App\Models\Flight;

class SearchFlightsController extends Controller
{
    public function index(SearchFlightsRequest $request, RyanairApi $ryanairApi)
    {
        $validated = $request->validated();

        $searchParams = SearchParams::fromArray($validated);
        $flights = $ryanairApi->searchFlights($searchParams);

        foreach($flights as $flight) {
            if (Flight::where('flight_key', $flight->flight_key)->exists()) {
                continue;
            }

            // extract flight price from flight object
            $flightPrice = $flight->flightPrices;
            unset($flight->flightPrices);
            
            $flight->save();

            $flightPrice->flight_id = $flight->id;
            $flightPrice->save();
        }

        return new FlightCollection($flights);
    }
}
