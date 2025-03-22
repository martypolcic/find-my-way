<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FlightResourceFirst extends JsonResource
{
    public function toArray($request)
    {
        return [
            'flightId' => $this->id,
            'route' => $this->departure_airport . ' - ' . $this->arrival_airport,
            'departure' => $this->departure_date,
            'arrival' => $this->arrival_date,
            'price' => [
                'amount' => $this->price,
                'currency' => 'EUR'
            ]
        ];
    }
}

