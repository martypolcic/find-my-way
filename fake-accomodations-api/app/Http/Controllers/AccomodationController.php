<?php

namespace App\Http\Controllers;

use App\Models\Accomodation;
use App\Http\Controllers\Controller;
use App\Http\Requests\SearchAccomodationsRequest;
use App\Http\Resources\AccomodationResourceFirst;
use App\Http\Resources\AccomodationResourceSecond;
use App\Services\AccomodationService;
use App\Models\City;

class AccomodationController extends Controller
{
    public function index()
    {
        return response()->json(Accomodation::all());
    }

    public function getAccomodations(SearchAccomodationsRequest $request)
    {
        $validated = $request->validated();

        $city = City::query()
            ->where('name', $validated['city'])
            ->first();
        if (!$city) {
            return response()->json(['error' => 'City not found'], 404);
        }

        $accomodationService = new AccomodationService();
        $accomodationService->generateAccomodationsForCity($city);
        $accomodationService->generateAccomodationBookings($validated['city'], $validated['check_in'], $validated['check_out']);

        $accomodations = Accomodation::query()
            ->whereHas('city', function ($query) use ($validated) {
                $query->where('name', $validated['city']);
            })
            ->with(['bookings' => function ($query) use ($validated) {
                $query->where('check_in_date', '>=', $validated['check_in'])
                    ->where('check_out_date', '<=', $validated['check_out'])
                    ->where('available', true);
            }])
            ->get();

        return config('app.fake_accomodations_api_version') === 'ALPHA'
            ? AccomodationResourceFirst::collection($accomodations)
            : AccomodationResourceSecond::collection($accomodations);
    }
}
