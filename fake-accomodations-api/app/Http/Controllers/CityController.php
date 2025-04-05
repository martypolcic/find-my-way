<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Http\Controllers\Controller;
use App\Integrations\OurAirportsApi;

class CityController extends Controller
{
    public function index()
    {
        return response()->json(City::all());
    }

    public function populateCities()
    {
        $ourAirportsApi = new OurAirportsApi();
        $ourAirportsApi->processCity();

        return response()->json(['message' => 'Cities populated successfully']);
    }
}
