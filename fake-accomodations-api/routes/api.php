<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CityController;
use App\Http\Controllers\AccomodationController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/populate-cities', [CityController::class, 'populateCities']);
Route::get('accomodation-offers', [AccomodationController::class, 'getAccomodations']);
