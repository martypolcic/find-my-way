<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccomodationResourceFirst extends JsonResource
{
    public function toArray($request)
    {
        return [
            'title' => $this->name,
            'Location' => $this->city->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'maxVisitors' => $this->capacity,
            'numberOfRooms' => $this->rooms,
            'offers' => $this->bookings->map(function ($booking) {
                return [
                    'booking_start_date' => $booking->check_in_date,
                    'booking_end_date' => $booking->check_out_date,
                    'price' => $booking->total_price . ' ' . $booking->currency,
                ];
            }),
        ];
    }
}