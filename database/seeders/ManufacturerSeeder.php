<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Ranium\SeedOnce\Traits\SeedOnce;

class ManufacturerSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();
        User::create([
            'username' => 'wxHNrjioxjlr',
            'name' => 'Nimish Jeweltech',
            'first_name' => 'Nimish',
            'last_name' => 'Jeweltech',
            'email' => 'nimish@imaginarium.io',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_by' => $systemUser->id,
        ]);

        // Give Users Manufacturer Role
        $user = User::where('email', 'nimish@imaginarium.io')->first();
        $user->assignRole('manufacturer');

        // Create Manufacturer
        $user->manufacturer()->create([
            'id' => 1,
            'country_id' => 112,
            'language_id' => 45,
            'currency_id' => 2,
            'name' => 'Imaginarium Jeweltech',
            'address_line1' => '7th Floor , The Great Oasis , D-13 Road No 21 , Midc , Andheri (E)',
            'address_line2' => null,
            'house_number' => '7th Floor, The Great Oasis',
            'postal_code' => '400093',
            'country_code' => 'IN',
            'city_id' => 27,
            'state_id' => 16,
            'contact_name_1' => 'Nimish',
            'phone_1' => '+31617264026',
            'email' => 'oscar@castimize.com',
            'billing_email' => 'nimish@imaginarium.io',
            'can_handle_own_shipping' => 1,
        ]);
    }
}
