<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /** @use HasFactory<\Database\Factories\FlightFactory> */
    use HasFactory;

    protected array $fillable = [
        'departureAirportIataCode'
    ];

    function departureAirport() {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    function arrivalAirport() {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    function flightPrices() {
        return $this->hasMany(FlightPrice::class);
    }
}
