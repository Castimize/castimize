<?php

namespace Database\Factories;

use App\Enums\Admin\OrderStatusesEnum;
use App\Models\OrderStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderStatus>
 */
class OrderStatusFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => OrderStatusesEnum::InQueue->name,
            'slug' => OrderStatusesEnum::InQueue->value,
            'end_status' => false,
        ];
    }
}
