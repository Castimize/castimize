<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\ShippingFee;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderQueue>
 */
class OrderQueueFactory extends Factory
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
            'upload_id' => Upload::factory(),
            'order_id' => Order::factory(),
            'shipping_fee_id' => ShippingFee::factory(),
            'due_date' => now()->addDays(5),
            'final_arrival_date' => now()->addDays(10),
            'contract_date' => now(),
            'manufacturer_costs' => fake()->numberBetween(100, 1000),
            'currency_code' => 'USD',
            'status_manual_changed' => false,
        ];
    }
}
