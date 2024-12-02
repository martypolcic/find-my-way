<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Airport>
 */
class AirportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $city = $this->faker->unique()->city;
        
        return [
            'iataCode' => $this->faker->unique()->regexify('[A-Z]{3}'),
            'airportName' => $city . ' Airport',
            'cityName' => $city,
            'countryName' => $this->faker->unique()->country,
        ];
    }
}
