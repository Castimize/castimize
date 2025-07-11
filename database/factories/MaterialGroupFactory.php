<?php

namespace Database\Factories;

use App\Models\MaterialGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialGroup>
 */
class MaterialGroupFactory extends Factory
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
        ];
    }
}
