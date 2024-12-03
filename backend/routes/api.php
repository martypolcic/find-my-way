<?php

use App\Http\Controllers\Api\V1\AirportController;
use App\Http\Controllers\Api\V1\FlightController;
use App\Http\Controllers\Api\V1\FlightPriceController;
use App\Http\Controllers\Api\V1\SearchFlightsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(
    [
        'prefix' => 'v1',
        'namespace' => 'App\Http\Controllers\Api\V1'
    ],
    function () {
        Route::apiResource('airports', AirportController::class)->only(['index', 'show', 'store']); 
        Route::apiResource('flights', FlightController::class)->only(['index', 'show']);
        Route::apiResource('flight-prices', FlightPriceController::class)->only(['index', 'show']);
        Route::get('search-flights', [SearchFlightsController::class, 'index']);
    }
);
