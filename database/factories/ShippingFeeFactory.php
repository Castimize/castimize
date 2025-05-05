<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\LogisticsZone;
use App\Models\ShippingFee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShippingFee>
 */
class ShippingFeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'logistics_zone_id' => LogisticsZone::factory(),
            'currency_id' => Currency::factory(),
            'name' => fake()->name,
            'default_rate' => fake()->numberBetween(100, 1000),
            'currency_code' => fake()->currencyCode,
            'default_lead_time' => fake()->numberBetween(1, 5),
        ];
    }
}
