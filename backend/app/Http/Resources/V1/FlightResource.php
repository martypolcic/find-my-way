<?php

namespace App\Http\Resources\V1;

use App\Models\Airport;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FlightResource extends JsonResource
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
            'flightNumber' => $this->flight_number,
            'flightKey' => $this->flight_key,
            'departureDate' => $this->departure_date,
            'arrivalDate' => $this->arrival_date,
            'departureAirportId' => $this->departure_airport_id,
            'departureAirportIataCode' => AirportResource::make(Airport::find($this->departure_airport_id))->iata_code,
            'arrivalAirportId' => $this->arrival_airport_id,
            'arrivalAirportIataCode' => AirportResource::make(Airport::find($this->arrival_airport_id))->iata_code,
            'airlineId' => $this->airline_id,
            'price_value' => FlightPriceResource::make($this->resource->latestPrice())->price_value,
            'currency_code' => FlightPriceResource::make($this->resource->latestPrice())->currency_code,
            
        ];
    }
}
