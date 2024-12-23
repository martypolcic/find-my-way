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
            'iata_code' => $this->faker->unique()->regexify('[A-Z]{3}'),
            'airport_name' => $city . ' Airport',
            'country_name' => $this->faker->unique()->country,
            'latitude_deg' => $this->faker->latitude,
            'longitude_deg' => $this->faker->longitude,
        ];
    }
}
