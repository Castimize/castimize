<?php

namespace Database\Factories;

use App\Models\Country;
use App\Models\Currency;
use App\Models\Material;
use App\Models\PaymentFee;
use App\Models\Price;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentFee>
 */
class PaymentFeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'currency_id' => Currency::factory(),
            'payment_method' => 'card',
            'fee' => 2500,
        ];
    }
}
