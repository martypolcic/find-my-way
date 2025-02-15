<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Integrations\OurAirports\OurAirports;
use App\Models\Airport;

class UpdateAirportsController extends Controller
{
    public function update()
    {
        if (!auth('web')->user()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        $airportCount = count(Airport::all());
        $ourAirports = new OurAirports();
        $ourAirports->updateAirports();
        
        return response()->json([
            'message' => 'Airports updated',
            'newAirports' => count(Airport::all()) - $airportCount
        ]);
    }
}
