<?php

use App\Http\Controllers\Api\V1\AirportController;
use App\Http\Controllers\Api\V1\FlightController;
use App\Http\Controllers\Api\V1\FlightPriceController;
use App\Http\Controllers\Api\V1\SearchFlightsController;
use App\Http\Controllers\Api\V1\UpdateAirportsController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::group(
    [
        'prefix' => 'v1',
        'namespace' => 'App\Http\Controllers\Api\V1'
    ],
    function () {
        Route::apiResource('airports', AirportController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::apiResource('flights', FlightController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::apiResource('flight-prices', FlightPriceController::class)->only(['index', 'show']);
        Route::get('search-flights', [SearchFlightsController::class, 'index']);
        Route::get('update-airports', [UpdateAirportsController::class, 'update']);
        Route::get('search-airports', [AirportController::class, 'search']);
    }
);

Route::post('/register', [RegisteredUserController::class, 'store']);
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);