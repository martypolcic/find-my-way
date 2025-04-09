<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchFlightsRequest;
use App\Http\Resources\V1\FlightCollection;
use App\Integrations\Params\FlightsSearchParams;
use App\Services\ApiService;
use App\Services\AirportService;
use App\Models\Flight;
use App\Services\ProviderServiceService;

class SearchFlightsController extends Controller
{
    public function index(SearchFlightsRequest $request)
    {
        $validated = $request->validated();
        $searchParams = FlightsSearchParams::fromArray($validated);

        $apiService = new ApiService();
        $apiService->searchFlights($searchParams);

        $providerServiceService = new ProviderServiceService();
        $activeFlightProviders = $providerServiceService->getActiveFlightProviders();
        
        $flights = Flight::query()
            ->where('departure_airport_id', AirportService::getAirportIdByIata($searchParams->getDepartureAirportIataCode()))
            ->whereDate('departure_date', $searchParams->getDepartureDate()->format('Y-m-d'))
            ->whereIn('provider_id', $activeFlightProviders)
            ->get();

        return new FlightCollection($flights);
    }
}
