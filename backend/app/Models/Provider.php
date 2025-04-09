<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function services()
    {
        return $this->hasMany(ProviderService::class);
    }
    
    public function flightServices()
    {
        return $this->services()->where('service_type', 'flight');
    }
    
    public function accomodationServices()
    {
        return $this->services()->where('service_type', 'accomodation');
    }
}
