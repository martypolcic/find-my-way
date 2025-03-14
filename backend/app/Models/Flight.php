<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    /** @use HasFactory<\Database\Factories\FlightFactory> */
    use HasFactory;

    protected $fillable = [
        'flight_number',
        'flight_key',
        'departure_date',
        'arrival_date',
        'departure_airport_id',
        'arrival_airport_id',
        'airline_id',
        'provider_id',
    ];

    protected $casts = [
        'departure_date' => 'datetime',
        'arrival_date' => 'datetime',
    ];

    function departureAirport() {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    function arrivalAirport() {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }

    function airline() {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    function provider() {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    function flightPrices() {
        return $this->hasMany(FlightPrice::class);
    }

    function latestPrice() {
        return $this->flightPrices()->latest()->first();
    }
}
