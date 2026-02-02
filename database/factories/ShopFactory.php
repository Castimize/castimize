<?php

namespace Database\Factories;

use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Shop;
use App\Models\ShopOwner;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
class ShopFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_owner_id' => ShopOwner::factory(),
            'shop' => ShopOwnerShopsEnum::Etsy->value,
            'shop_oauth' => [
                'shop_id' => fake()->numberBetween(10000000, 99999999),
                'access_token' => fake()->sha256(),
                'refresh_token' => fake()->sha256(),
            ],
            'active' => true,
        ];
    }

    /**
     * Indicate that the shop is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    /**
     * Configure the shop as an Etsy shop.
     */
    public function etsy(): static
    {
        return $this->state(fn (array $attributes) => [
            'shop' => ShopOwnerShopsEnum::Etsy->value,
        ]);
    }
}
