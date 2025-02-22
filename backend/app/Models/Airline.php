<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airline extends Model
{
    /** @use HasFactory<\Database\Factories\AirportFactory> */
    use HasFactory;

    protected $fillable = [
        'iata_code',
        'icao_code',
        'airline_name',
        'active',
    ];
    
    function flights() {
        return $this->hasMany(Flight::class);
    }
}