<?php

namespace Database\Factories;

use App\Models\Complaint;
use App\Models\ComplaintReason;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Complaint>
 */
class ComplaintFactory extends Factory
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
            'complaint_reason_id' => ComplaintReason::factory(),
            'upload_id' => Upload::factory(),
            'order_id' => Order::factory(),
            'reason' => fake()->sentence(8),
            'description' => fake()->text(250),
        ];
    }
}
