<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Airport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AirportRequest;
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
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(AirportRequest $request)
    {
        return new AirportResource(Airport::create($request->validated()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AirportRequest $request, Airport $airport)
    {
        $airport->update($request->validated());
        return new AirportResource($airport);
    }
}
