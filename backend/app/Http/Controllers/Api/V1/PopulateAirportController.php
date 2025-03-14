<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\AirportService;

class PopulateAirportController extends Controller
{
      /**
       * Populate airports from external API.
       * 
       * @return \Illuminate\Http\Response
       */
      public function populateAirports()
      {
            AirportService::populateAirports();
            return response()->json(['message' => 'Airports populated successfully.']);
      }
}
