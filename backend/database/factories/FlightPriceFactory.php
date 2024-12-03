<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FlightPrice>
 */
class FlightPriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'price_value' => $this->faker->randomFloat(2, 0, 1000),
            'currency_code' => $this->faker->currencyCode(),
        ];
    }
}
