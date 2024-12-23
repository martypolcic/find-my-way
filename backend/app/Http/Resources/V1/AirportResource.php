<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AirportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'iataCode' => $this->iata_code,
            'airportName' => $this->airport_name,
            'countryName' => $this->country_name,
            'latitudeDeg' => $this->latitude_deg,
            'longitudeDeg' => $this->longitude_deg,
        ];
    }
}
