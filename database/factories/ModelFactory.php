<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class ModelFactory extends Factory
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
            'material_id' => Material::factory(),
            'model_name' => fake()->name,
            'name' => fake()->name,
            'file_name' => 'test.stl',
            'thumb_name' => 'test.stl.thumb.png',
            'model_volume_cc' => fake()->randomFloat(5, 0.12, 100.00),
            'model_x_length' => fake()->randomFloat(5, 0.12, 100.00),
            'model_y_length' => fake()->randomFloat(5, 0.12, 100.00),
            'model_z_length' => fake()->randomFloat(5, 0.12, 100.00),
            'model_surface_area_cm2' => fake()->randomFloat(5, 0.12, 100.00),
            'model_parts' => 1,
            'model_box_volume' => fake()->randomFloat(5, 0.12, 100.00),
            'model_scale' => 1,
        ];
    }
}
