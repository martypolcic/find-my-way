<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Airport;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AirportRequest;
use App\Http\Requests\V1\SearchAirportRequest;
use App\Http\Resources\V1\AirportCollection;
use App\Http\Resources\V1\AirportResource;

class AirportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new AirportCollection(Airport::all());
    }

    public function search(SearchAirportRequest $request)
    {
        $search = $request->validated('search');

        return new AirportCollection(Airport::where('airport_name', 'like', "%{$search}%")
            ->orWhere('country_name', 'like', "%{$search}%")
            ->orWhere('city_name', 'like', "%{$search}%")
            ->get());
    }

    /**
     * Display the specified resource.
     */
    public function show(Airport $airport)
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new AirportResource($airport);
    }
    
    /**
     * Store a newly created resource in storage.
     */
    public function store(AirportRequest $request)
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new AirportResource(Airport::create($request->validated()));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AirportRequest $request, Airport $airport)
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $airport->update($request->validated());
        return new AirportResource($airport);
    }

     /**
      * Remove the specified resource from storage.
      */
      public function destroy(Airport $airport)
      {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
          $airport->delete();
          return response()->noContent();
      }
}
