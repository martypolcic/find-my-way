<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccomodationBooking extends Model
{
    use HasFactory;

    protected $fillable = [
        'accomodation_id',
        'check_in_date',
        'check_out_date',
        'total_price',
        'currency',
        'available',
    ];

    public function accomodation()
    {
        return $this->belongsTo(Accomodation::class);
    }
}
