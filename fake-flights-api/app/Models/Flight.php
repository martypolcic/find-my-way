<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flight extends Model
{
    use HasFactory;

    protected $fillable = [
        'departure_airport',
        'arrival_airport',
        'departure_date',
        'arrival_date',
        'price',
        'currency',
    ];

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport', 'iata_code');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport', 'iata_code');
    }
}