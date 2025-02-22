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
        return new FlightCollection(Flight::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Flight $flight)
    {
        return new FlightResource($flight);
    }
}
