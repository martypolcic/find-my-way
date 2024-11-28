<?php

namespace Database\Seeders;

use App\Models\Airport;
use App\Models\Flight;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FlightSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Airport::factory()
            ->count(100)
            ->create();

        // TODO: find a more efficient way to create flights
        for ($i = 0; $i < 1000; $i++) {
            $departureAirport = Airport::inRandomOrder()->first();
            $arrivalAirport = Airport::inRandomOrder()->where('id', '!=', $departureAirport->id)->first();

            Flight::factory()
                ->hasFlightPrices(3)
                ->create([
                    'departure_airport_id' => $departureAirport->id,
                    'arrival_airport_id' => $arrivalAirport->id,
                ]);
        }
    }
}
