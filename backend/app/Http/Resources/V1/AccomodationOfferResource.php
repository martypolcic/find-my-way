<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class AccomodationOfferResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'checkIn' => $this->check_in,
            'checkOut' => $this->check_out,
            'price' => $this->price,
            'currency' => $this->currency,
            'description' => $this->description,
        ];
    }
}
