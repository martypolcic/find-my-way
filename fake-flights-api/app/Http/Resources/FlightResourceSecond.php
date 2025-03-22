<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlightResourceSecond extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'origin' => $this->departure_airport,
            'destination' => $this->arrival_airport,
            'schedule' => [
                'departure_at' => $this->departure_date,
                'arrival_at' => $this->arrival_date
            ],
            'cost' => $this->price . ' EUR'
        ];
    }
}
