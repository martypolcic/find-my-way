<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightPrice extends Model
{
    /** @use HasFactory<\Database\Factories\FlightPriceFactory> */
    use HasFactory;

    function flight() {
        return $this->belongsTo(Flight::class);
    }
}
