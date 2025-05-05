<?php

namespace Database\Factories;

use App\Enums\Admin\OrderStatusesEnum;
use App\Models\OrderQueue;
use App\Models\OrderQueueStatus;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderQueueStatus>
 */
class OrderQueueStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_queue_id' => OrderQueue::factory(),
            'order_status_id' => OrderStatus::factory(),
            'status' => OrderStatusesEnum::InQueue->name,
            'slug' => OrderStatusesEnum::InQueue->value,
            'target_date' => now()->addDays(10),
        ];
    }
}
