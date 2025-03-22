<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchFlightsRequest;
use App\Http\Resources\FlightResourceFirst;
use App\Http\Resources\FlightResourceSecond;
use App\Models\Flight;
use App\Services\FlightService;

class FlightController extends Controller
{
    protected $flightService;

    public function __construct(FlightService $flightService)
    {
        $this->flightService = $flightService;
    }

    public function getFlights(SearchFlightsRequest $request)
    {
        $validated = $request->validated();

        $this->flightService->generateFlightsForDate($validated['departureDate']);

        $flights = Flight::where('departure_airport', $validated['departureAirportIataCode'])
            ->whereDate('departure_date', $validated['departureDate'])
            ->get();

        return config('app.fake_flights_api_version') === 'ALPHA'
            ? FlightResourceFirst::collection($flights)
            : FlightResourceSecond::collection($flights);
    }
}
