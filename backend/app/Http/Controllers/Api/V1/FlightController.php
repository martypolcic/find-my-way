<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Flight;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\FlightCollection;
use App\Http\Resources\V1\FlightResource;
use App\Http\Requests\V1\FlightRequest;
use App\Models\Airport;

class FlightController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new FlightCollection(Flight::all());
    }

    /**
     * Display the specified resource.
     */
    public function show(Flight $flight)
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return new FlightResource($flight);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FlightRequest $request)
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $departureAirport = $request->validated()['departure_airport_iata_code'];
        $arrivalAirport = $request->validated()['arrival_airport_iata_code'];

        $departureAirportId = Airport::where('iata_code', $departureAirport)->first()->id;
        $arrivalAirportId = Airport::where('iata_code', $arrivalAirport)->first()->id;
        
        return new FlightResource(Flight::create([
            'flight_key' => $request->validated()['flight_key'],
            'flight_number' => $request->validated()['flight_number'],
            'departure_airport_id' => $departureAirportId,
            'arrival_airport_id' => $arrivalAirportId,
            'departure_date' => $request->validated()['departure_date'],
            'arrival_date' => $request->validated()['arrival_date'],
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FlightRequest $request, Flight $flight)
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $departureAirport = $request->validated()['departure_airport_iata_code'];
        $arrivalAirport = $request->validated()['arrival_airport_iata_code'];

        $departureAirportId = Airport::where('iata_code', $departureAirport)->first()->id;
        $arrivalAirportId = Airport::where('iata_code', $arrivalAirport)->first()->id;

        $flight->update([
            'flight_key' => $request->validated()['flight_key'],
            'flight_number' => $request->validated()['flight_number'],
            'departure_airport_id' => $departureAirportId,
            'arrival_airport_id' => $arrivalAirportId,
            'departure_date' => $request->validated()['departure_date'],
            'arrival_date' => $request->validated()['arrival_date'],
        ]);

        return new FlightResource($flight);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Flight $flight)
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $flight->delete();
        return response()->json(null, 204);
    }
}
