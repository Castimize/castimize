<?php

namespace Database\Factories;

use App\Models\Manufacturer;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Rejection;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Rejection>
 */
class RejectionFactory extends Factory
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
            'order_queue_id' => OrderQueue::factory(),
            'order_id' => Order::factory(),
            'upload_id' => Upload::factory(),
            'reason_manufacturer' => fake()->sentence(),
            'note_manufacturer' => fake()->paragraph(),
            'note_castimize' => fake()->paragraph(),
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approved_at' => now(),
        ]);
    }

    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'declined_at' => now(),
        ]);
    }
}
