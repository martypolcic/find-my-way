<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Integrations\Params\AccomodationsSearchParams;
use App\Services\ApiService;
use App\Http\Requests\V1\SearchAccomodationsRequest;
use App\Http\Resources\V1\AccomodationResource;
use App\Models\Accomodation;
use App\Services\AirportService;

class SearchAccomodationsController extends Controller
{
    public function index(SearchAccomodationsRequest $request)
    {
        $validated = $request->validated();
        $searchParams = AccomodationsSearchParams::fromArray($validated);

        $apiService = new ApiService();
        $apiService->searchAccomodations($searchParams);

        $airport = AirportService::getAirportByIata($searchParams->getAirportIataCode());

        $accomodations = Accomodation::where('airport_id', $airport->id)
            ->with(['offers' => function($query) use ($searchParams) {
                $query->whereDate('check_in', $searchParams->getCheckInDate())
                    ->whereDate('check_out', $searchParams->getCheckOutDate());
            }])
            ->get();
        
        return AccomodationResource::collection($accomodations);
    }
}
