<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class ProviderService extends Model
{
    protected $fillable = [
        'provider_id',
        'service_type',
        'class_name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
    
    public function provider()
    {
        return $this->belongsTo(Provider::class);
    }
}