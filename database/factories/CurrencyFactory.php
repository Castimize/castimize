<?php

namespace Database\Factories;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Currency>
 */
class CurrencyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a unique code that doesn't exist in the database
        do {
            $code = fake()->currencyCode();
        } while (Currency::where('code', $code)->exists());

        return [
            'name' => $code,
            'code' => $code,
        ];
    }
}
