<?php
namespace App\Services;

use App\Models\Flight;
use Carbon\Carbon;
use App\Models\Airport;

class FlightService
{
    private const PRICE_PER_HOUR = 50;
    private const AIRPORTS_ALPHA = ['VIE', 'BTS', 'MAD', 'VLC', 'CRL', 'LHR', 'CDG', 'FRA', 'AMS', 'BCN', 'MUC', 'ATH', 'OTP', 'DUB', 'ZRH', 'WAW', 'OSL', 'GVA', 'LIS'];
    private const AIRPORTS_BETA = ['VIE', 'BTS', 'MAD', 'VLC', 'CRL', 'LHR', 'CDG', 'FRA', 'AMS', 'BCN', 'MUC', 'ATH', 'OTP', 'DUB', 'ZRH', 'PRG', 'CPH', 'HEL', 'BRU'];

    public function generateFlightsForDate($date)
    {
        $existingFlights = Flight::whereDate('departure_date', $date)->count();
        if ($existingFlights > 0) {
            return;
        }

        $apiVersion = config('app.fake_flights_api_version');

        $selectedAirports = Airport::query()
            ->whereIn('iata_code', $apiVersion === 'ALPHA' ? self::AIRPORTS_ALPHA : self::AIRPORTS_BETA)
            ->get();

        foreach ($selectedAirports as $departure) {
            $availableRoutes = $departure->departures()->inRandomOrder()->get();

            foreach ($availableRoutes as $route) {
                $flightsPerDestination = rand(1, 3);
                for ($i = 0; $i < $flightsPerDestination; $i++) {
                    $departureTime = Carbon::parse($date)->setTime(rand(5, 17), rand(0, 3) * 15);
                    $arrivalTime = $departureTime->copy()->addMinutes($route->duration);

                    Flight::create([
                        'departure_airport' => $departure->iata_code,
                        'arrival_airport' => $route->arrival_airport,
                        'departure_date' => $departureTime,
                        'arrival_date' => $arrivalTime,
                        'price' => $this->generateRandomPrice($route->duration),
                        'currency' => 'EUR',
                    ]);

                    // Return flight
                    $returnDepartureTime = $arrivalTime->copy()->addHours(rand(1, 3));
                    $returnArrivalTime = $returnDepartureTime->copy()->addMinutes($route->duration);

                    Flight::create([
                        'departure_airport' => $route->arrival_airport,
                        'arrival_airport' => $departure->iata_code,
                        'departure_date' => $returnDepartureTime,
                        'arrival_date' => $returnArrivalTime,
                        'price' => $this->generateRandomPrice($route->duration),
                        'currency' => 'EUR',
                    ]);
                }
            }
        }
    }

    private function generateRandomPrice($duration)
    {
        $basePrice = ($duration / 60) * self::PRICE_PER_HOUR;
        $randomFactor = $basePrice * (rand(-10, 10) / 100);

        return round($basePrice + $randomFactor, 2);
    }
}
