<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    /** @use HasFactory<\Database\Factories\AirportFactory> */
    use HasFactory;

    protected $fillable = [
        'iata_code',
        'airport_name',
        'country_name',
        'latitude_deg',
        'longitude_deg',
    ];

    public function isValid() {
        return 
            isset($this->iata_code) &&
            isset($this->airport_name) &&
            isset($this->country_name) &&
            isset($this->latitude_deg) &&
            isset($this->longitude_deg);
    }

    function flights() {
        return $this->hasMany(Flight::class);
    }

    function departureFlights() {
        return $this->hasMany(Flight::class, 'departure_airport_id');
    }

    function arrivalFlights() {
        return $this->hasMany(Flight::class, 'arrival_airport_id');
    }
}
