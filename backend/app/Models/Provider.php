<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Provider extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'class_name',
        'active',
    ];

    function flights() {
        return $this->hasMany(Flight::class, 'provider_id');
    }
}
