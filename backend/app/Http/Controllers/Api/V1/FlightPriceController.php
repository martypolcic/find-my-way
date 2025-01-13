<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\FlightPrice;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FlightPriceCollection;
use App\Http\Resources\V1\FlightPriceResource;

class FlightPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth('api')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new FlightPriceCollection(FlightPrice::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(FlightPrice $flightPrice)
    {
        if (!auth('api')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new FlightPriceResource($flightPrice);
    }
}
