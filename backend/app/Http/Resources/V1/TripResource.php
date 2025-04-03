<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

class TripResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return $this->resource['departureFlights']->groupBy([
            fn ($flight) => $flight->arrivalAirport->country,
            fn ($flight) => $flight->arrivalAirport->city
        ])->map(function ($cities, $country) {
            return $cities->map(function ($flights, $city) {
                $airportId = $flights->first()->arrival_airport_id;
                
                $accomodations = $this->resource['accomodations']->get($airportId, collect());
                
                return [
                    'departureFlights' => FlightResource::collection($flights),
                    'accomodations' => $accomodations->map(function($accomodation) {
                        return [
                            'accomodation' => new AccomodationResource($accomodation),
                            'offers' => AccomodationOfferResource::collection(
                                $accomodation->offers
                            )
                        ];
                    }),
                    'returnFlights' => FlightResource::collection(
                        $this->resource['returnFlights']->get($airportId, [])
                    )
                ];
            });
        });
    }
}
