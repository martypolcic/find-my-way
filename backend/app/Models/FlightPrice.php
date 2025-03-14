<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPrice extends Model
{
    /** @use HasFactory<\Database\Factories\FlightPriceFactory> */
    use HasFactory;

    protected $fillable = [
        'flight_id',
        'price_value',
        'currency_code',
    ];

    protected $casts = [
        'price_value' => 'float',
    ];
    
    function flight() {
        return $this->belongsTo(Flight::class);
    }
}
