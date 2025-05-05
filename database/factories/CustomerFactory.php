<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
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
            'wp_id' => fake()->numberBetween(1, 10000),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'company' => fake()->company,
            'email' => fake()->email,
            'phone' => fake()->phoneNumber,
            'vat_number' => 'NL866959300B01',
        ];
    }
}
