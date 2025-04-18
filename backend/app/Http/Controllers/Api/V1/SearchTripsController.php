<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchTripsRequest;
use App\Integrations\Params\TripsSearchParams;
use App\Services\ApiService;
use App\Services\AirportService;
use App\Models\Accomodation;
use App\Models\Flight;
use App\Http\Resources\V1\TripResource;
use App\Services\ProviderServiceService;

class SearchTripsController extends Controller
{
    public function index(SearchTripsRequest $request)
    {
        $validated = $request->validated();
        $tripsSearchParams = TripsSearchParams::fromArray($validated);

        $apiService = new ApiService();
        $apiService->searchTrips($tripsSearchParams);

        $departureAirportIataCode = $tripsSearchParams->getDepartureAirportIataCode();
        $departureAirportId = AirportService::getAirportByIata($departureAirportIataCode)->id;

        $providerService = new ProviderServiceService();
        $flightsProviders = $providerService->getActiveFlightProviders();
        $accomodationsProviders = $providerService->getActiveAccomodationProviders();


        $departureFlights = Flight::with(['arrivalAirport',])
            ->where('departure_airport_id', $departureAirportId)
            ->whereDate('departure_date', $tripsSearchParams->getDepartureDate())
            ->whereIn('provider_id', $flightsProviders)
            ->get();
        
        $accomodations = accomodation::with(['offers' => function($query) use ($tripsSearchParams) {
            $query->whereDate('check_in', $tripsSearchParams->getDepartureDate())
                ->whereDate('check_out', $tripsSearchParams->getReturnDate());
        }])
        ->whereIn('airport_id', 
            $departureFlights->pluck('arrival_airport_id')->unique()
        )
        ->whereIn('provider_id', $accomodationsProviders)
        ->get()
        ->filter(function ($accommodation) {
            return $accommodation->offers->isNotEmpty();
        })
        ->groupBy('airport_id');

        $returnFlights = Flight::whereIn('departure_airport_id', 
                $departureFlights->pluck('arrival_airport_id')->unique()
            )
            ->where('arrival_airport_id', $departureAirportId)
            ->whereDate('departure_date', $tripsSearchParams->getReturnDate())
            ->whereIn('provider_id', $flightsProviders)
            ->get()
            ->groupBy('departure_airport_id');

        return new TripResource([
            'departureFlights' => $departureFlights,
            'accomodations' => $accomodations,
            'returnFlights' => $returnFlights
        ]);
    }
}
