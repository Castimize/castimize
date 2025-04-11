<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\LogisticsZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Country>
 */
class CountryFactory extends Factory
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
            'name' => fake()->name,
            'alpha2' => 'NL',
            'alpha3' => 'NLD',
        ];
    }
}
