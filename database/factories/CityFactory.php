<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'lat' => fake()->latitude,
            'lng' => fake()->longitude,
            'name' => fake()->name,
            'slug' => fake()->slug,
            'state_id' => State::factory(),
            'country_id' => Country::factory(),
        ];
    }
}
