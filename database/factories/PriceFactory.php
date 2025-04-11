<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Material;
use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Price>
 */
class PriceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'material_id' => Material::factory(),
            'country_id' => Country::factory(),
            'currency_id' => Currency::factory(),
            'setup_fee' => true,
            'setup_fee_amount' => 2500,
            'minimum_per_stl' => 0.55,
            'price_minimum_per_stl' => 30,
            'price_volume_cc' => 1500,
            'price_surface_cm2' => 1300,
            'fixed_fee_per_part' => 0,
            'currency_code' => 'USD',
        ];
    }
}
