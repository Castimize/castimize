<?php

namespace Database\Factories;

use App\Models\CurrencyHistoryRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CurrencyHistoryRate>
 */
class CurrencyHistoryRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'base_currency' => 'EUR',
            'convert_currency' => fake()->randomElement(['USD', 'GBP']),
            'rate' => fake()->randomFloat(4, 0.5, 2.0),
            'historical_date' => fake()->date(),
        ];
    }
}
