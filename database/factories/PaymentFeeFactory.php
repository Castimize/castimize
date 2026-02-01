<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\PaymentFee;
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
