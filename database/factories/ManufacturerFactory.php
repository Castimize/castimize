<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\Manufacturer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Manufacturer>
 */
class ManufacturerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'country_id' => Country::factory(),
            'language_id' => Language::factory(),
            'currency_id' => Currency::factory(),
            'name' => fake()->name,
            'phone_1' => fake()->phoneNumber,
            'email' => fake()->email,
            'can_handle_own_shipping' => true,
        ];
    }
}
