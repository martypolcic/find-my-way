<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchFlightsRequest;
use App\Http\Resources\V1\FlightCollection;
use App\Integrations\Params\SearchParams;
use App\Services\ApiService;
use App\Services\AirportService;
use App\Models\Flight;

class SearchFlightsController extends Controller
{
    public function index(SearchFlightsRequest $request)
    {
        $validated = $request->validated();
        $searchParams = SearchParams::fromArray($validated);

        $apiService = new ApiService();
        $apiService->searchFlights($searchParams);

        $departureAirportId = AirportService::getAirportIdByIata($searchParams->getDepartureAirportIataCode());
        $departureDate = $searchParams->getDepartureDate()->format('Y-m-d');
        $flights = Flight::where('departure_airport_id', $departureAirportId)
            ->whereDate('departure_date', $departureDate)
            ->get();

        return new FlightCollection($flights);
    }
}
