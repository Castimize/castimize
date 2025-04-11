<?php

namespace Database\Factories;

use App\Enums\Admin\ShippingServiceLevelTokenEnum;
use App\Models\LogisticsZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LogisticsZone>
 */
class LogisticsZoneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name,
            'shipping_servicelevel_token' => fake()->randomElement(ShippingServiceLevelTokenEnum::cases()),
        ];
    }
}
