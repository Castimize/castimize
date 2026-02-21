<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\ShopOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ShopOwner>
 */
class ShopOwnerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'active' => true,
        ];
    }

    /**
     * Indicate that the shop owner is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }
}
