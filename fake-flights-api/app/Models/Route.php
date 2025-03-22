<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = ['departure_airport', 'arrival_airport', 'duration'];

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport', 'iata_code');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport', 'iata_code');
    }
}
