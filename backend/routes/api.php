<?php

use App\Http\Controllers\Api\V1\AirportController;
use App\Http\Controllers\Api\V1\FlightController;
use App\Http\Controllers\Api\V1\FlightPriceController;
use App\Http\Controllers\Api\V1\PopulateAirportController;
use App\Http\Controllers\Api\V1\ProviderServiceController;
use App\Http\Controllers\Api\V1\SearchFlightsController;
use App\Http\Controllers\Api\V1\SearchAirportController;
use App\Http\Controllers\Api\V1\SearchAccomodationsController;
use App\Http\Controllers\Api\V1\SearchTripsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;

Route::middleware('web')->post('/login', [AuthController::class, 'login']);

Route::prefix('v1')->group( 
    function () {
        Route::apiResource('airports', AirportController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
        Route::apiResource('flights', FlightController::class)->only(['index', 'show']);
        Route::apiResource('flight-prices', FlightPriceController::class)->only(['index', 'show']);
        Route::apiResource('search-airports', SearchAirportController::class)->only(['index']);
        Route::get('populate-airports', [PopulateAirportController::class, 'populateAirports']);
        Route::get('search-flights', [SearchFlightsController::class, 'index']);
        Route::get('search-accomodations', [SearchAccomodationsController::class, 'index']);
        Route::get('search-trips', [SearchTripsController::class, 'index']);
    }
);

Route::middleware([
    EnsureFrontendRequestsAreStateful::class,
    'auth:sanctum'
])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    Route::get('/provider-services', [ProviderServiceController::class, 'index']);
    Route::patch('/provider-services/{id}/toggle', [ProviderServiceController::class, 'toggle']);
});