<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Manufacturer;
use App\Models\ManufacturerCost;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManufacturerCost>
 */
class ManufacturerCostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'manufacturer_id' => Manufacturer::factory(),
            'material_id' => Material::factory(),
            'currency_id' => Currency::factory(),
            'production_lead_time' => fake()->numberBetween(5, 10),
            'shipment_lead_time' => fake()->numberBetween(5, 9),
            'setup_fee' => true,
            'setup_fee_amount' => 1000,
            'costs_volume_cc' => 1200,
            'costs_minimum_per_stl' => 1100,
            'costs_surface_cm2' => 1300,
            'currency_code' => 'USD',
            'active' => true,
        ];
    }
}
