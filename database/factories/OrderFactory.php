<?php

namespace Database\Factories;

use App\Enums\Admin\PaymentIssuersEnum;
use App\Enums\Woocommerce\WcOrderStatesEnum;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
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
            'country_id' => Country::factory(),
            'currency_id' => Currency::factory(),
            'source' => fake()->randomElement(['wp', 'etsy']),
            'order_number' => fake()->randomNumber(),
            'order_key' => fake()->randomNumber(),
            'first_name' => fake()->firstName,
            'last_name' => fake()->lastName,
            'email' => fake()->email,
            'status' => fake()->randomElement(WcOrderStatesEnum::cases()),
            'billing_first_name' => fake()->firstName,
            'billing_last_name' => fake()->lastName,
            'billing_company' => fake()->company,
            'billing_phone_number' => fake()->phoneNumber,
            'billing_email' => fake()->email,
            'billing_address_line1' => fake()->address,
            'billing_postal_code' => fake()->postcode,
            'billing_city' => fake()->city,
            'billing_country' => fake()->countryCode,
            'shipping_first_name' => fake()->firstName,
            'shipping_last_name' => fake()->lastName,
            'shipping_company' => fake()->company,
            'shipping_phone_number' => fake()->phoneNumber,
            'shipping_email' => fake()->email,
            'shipping_address_line1' => fake()->address,
            'shipping_postal_code' => fake()->postcode,
            'shipping_city' => fake()->city,
            'shipping_country' => fake()->countryCode,
            'shipping_fee' => fake()->numberBetween(100, 1000),
            'shipping_fee_tax' => 0,
            'total' => fake()->numberBetween(1001, 10000),
            'total_tax' => 0,
            'production_cost' => 0,
            'production_cost_tax' => 0,
            'tax_percentage' => 21,
            'currency_code' => fake()->currencyCode,
            'order_parts' => fake()->numberBetween(1, 10),
            'payment_method' => fake()->name,
            'payment_issuer' => fake()->randomElement(PaymentIssuersEnum::cases()),
            'is_paid' => true,
            'paid_at' => now(),
            'has_manual_refund' => 0,
        ];
    }
}
