<?php

namespace App\Services;

use App\Models\FlightPrice;

class FlightPriceService
{
    public static function storeFlightPrice(int $flightId, float $priceValue, string $currencyCode): void
    {
        $latestPrice = FlightPrice::where('flight_id', $flightId)
            ->latest()
            ->first();
            
        if (!$latestPrice || $latestPrice->price_value !== $priceValue) {
            FlightPrice::create([
                'flight_id' => $flightId,
                'price_value' => $priceValue,
                'currency_code' => $currencyCode,
            ]);
        }
    }
}
