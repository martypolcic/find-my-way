<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accomodation extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'name',
        'airport_id',
        'latitude',
        'longitude',
        'provider_id',
        'price_level',
        'description',
    ];

    public function airport()
    {
        return $this->belongsTo(Airport::class, 'airport_id');
    }

    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    public function offers()
    {
        return $this->hasMany(AccomodationOffer::class);
    }
}
