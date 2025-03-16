<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\SearchAirportRequest;
use App\Http\Resources\V1\AirportCollection;
use App\Models\Airport;

class SearchAirportController extends Controller
{
    public function index(SearchAirportRequest $request)
    {
        $query = $request->validated()['query'];

        $airports = Airport::where('active', true)
            ->whereAny([
                'name',
                'city',
                'country',
            ], 'like', '%' . $query . '%')
            ->get();

        return new AirportCollection($airports);
    }
}
