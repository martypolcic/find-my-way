<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Airport;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AirportCollection;
use App\Http\Resources\V1\AirportResource;

class AirportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return new AirportCollection(Airport::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Airport $airport)
    {
        return new AirportResource($airport);
    }
}
