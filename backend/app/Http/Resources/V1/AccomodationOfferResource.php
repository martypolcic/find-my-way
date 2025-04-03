<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

// app/Http/Resources/AccommodationOfferResource.php
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
            'accomodation' => new AccomodationResource($this->accommodation),
        ];
    }
}
