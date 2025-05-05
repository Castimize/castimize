<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Material;
use App\Models\MaterialGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'material_group_id' => MaterialGroup::factory(),
            'currency_id' => Currency::factory(),
            'name' => fake()->name,
            'dc_lead_time' => fake()->numberBetween(15, 20),
            'fast_delivery_lead_time' => fake()->numberBetween(10, 15),
            'fast_delivery_fee' => 10000,
            'currency_code' => fake()->currencyCode,
            'hs_code_description' => fake()->text(200),
            'hs_code' => 7113195090,
            'minimum_x_length' => 0.1,
            'maximum_x_length' => 10,
            'minimum_y_length' => 0.1,
            'maximum_y_length' => 10,
            'minimum_z_length' => 0.1,
            'maximum_z_length' => 10,
            'minimum_volume' => 0.02,
            'maximum_volume' => 1500,
            'minimum_box_volume' => 0.05,
            'maximum_box_volume' => 1500,
            'density' => 8.5,
        ];
    }
}
