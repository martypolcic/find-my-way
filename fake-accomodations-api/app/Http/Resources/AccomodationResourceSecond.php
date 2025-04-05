<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccomodationResourceSecond extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'city' => $this->city->name,
            'geolocation' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ],
            'capacity' => $this->capacity,
            'rooms' => $this->rooms,
            'bookings' => $this->bookings->map(function ($booking) {
                return [
                    'check_in' => $booking->check_in_date,
                    'check_out' => $booking->check_out_date,
                    'price' => [
                        'amount' => $booking->total_price,
                        'currency' => $booking->currency,
                    ],
                    'available' => $booking->available,
                ];
            }),
        ];
    }
}