<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $iata_code
 * @property string $icao_code
 * @property string $airline_name
 */
class Airline extends Model
{
    /** @use HasFactory<\Database\Factories\AirportFactory> */
    use HasFactory;

    protected $fillable = [
        'iata_code',
        'icao_code',
        'name',
    ];
    
    function flights() {
        return $this->hasMany(Flight::class);
    }
}