<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        //        $faker = fake(LocaleEnum::from($attributes['locale_code'])->getFakerLocale());
        //        return [
        //            'zip_code' => $faker->postcode(),
        //            'house_nr' => $faker->randomNumber(2, true),
        //            'street' => $faker->streetName(),
        //            'city' => $faker->city(),
        //            'phone' => $faker->phoneNumber(),
        //        ];

        return [
            'lat' => fake()->latitude,
            'lng' => fake()->longitude,
            'address_line1' => fake()->streetAddress,
            'postal_code' => fake()->postcode,
            'city_id' => City::factory(),
            'state_id' => State::factory(),
            'country_id' => Country::factory(),
        ];
    }
}
