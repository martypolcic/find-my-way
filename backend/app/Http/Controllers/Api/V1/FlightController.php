<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Flight;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FlightCollection;
use App\Http\Resources\V1\FlightResource;

class FlightController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth('api')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new FlightCollection(Flight::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Flight $flight)
    {
        if (!auth('api')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new FlightResource($flight);
    }
}
