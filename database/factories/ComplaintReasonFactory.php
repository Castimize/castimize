<?php

namespace Database\Factories;

use App\Models\ComplaintReason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ComplaintReason>
 */
class ComplaintReasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reason' => fake()->sentence(8),
        ];
    }
}
