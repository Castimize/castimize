<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'iso' => fake()->languageCode,
            'locale' => fake()->locale,
            'local_name' => fake()->name,
            'en_name' => fake()->name,
        ];
    }
}
