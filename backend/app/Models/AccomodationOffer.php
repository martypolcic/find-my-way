<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccomodationOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'accomodation_id',
        'external_id',
        'check_in',
        'check_out',
        'price',
        'currency',
        'description',
    ];

    protected $casts = [
        'check_in' => 'datetime',
        'check_out' => 'datetime',
    ];

    public function accommodation()
    {
        return $this->belongsTo(Accomodation::class);
    }
}
