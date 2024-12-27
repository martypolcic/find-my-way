<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Integrations\AviationEdge\AviationEdgeAirports;

class UpdateAirportsController extends Controller
{
    public function update()
    {
        $aviationEdgeAirports = new AviationEdgeAirports();
        $aviationEdgeAirports->updateAviationAirports();

        return response()->json(['message' => 'Airports sucesfully updated'], 200);
    }
}
