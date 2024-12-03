<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flight>
 */
class FlightFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $flightNumber = $this->faker->unique()->regexify('[A-Z]{2}[0-9]{4}');
        $departureDate = $this->faker->dateTimeBetween('now', '+3 months');
        
        return [
            'flight_number' => $flightNumber,
            'flight_key' => $flightNumber . "~ ~~" . $departureDate->format('Y-m-d H:i:s'),
            'departure_date' => $departureDate,
            'arrival_date' => $this->faker->dateTimeBetween($departureDate, '+3 months'),
        ];
    }
}
