<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = [
        'iata_code',
        'icao_code',
        'name',
        'city',
        'country',
    ];

    public function departures() {
        return $this->hasMany(Route::class, 'departure_airport', 'iata_code');
    }

    public function arrivals() {
        return $this->hasMany(Route::class, 'arrival_airport', 'iata_code');
    }
}