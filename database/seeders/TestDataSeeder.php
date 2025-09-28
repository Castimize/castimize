<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\City;
use App\Models\ComplaintReason;
use App\Models\Country;
use App\Models\Customer;
use App\Models\LogisticsZone;
use App\Models\Manufacturer;
use App\Models\MaterialGroup;
use App\Models\Service;
use App\Models\State;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use JsonException;
use Ranium\SeedOnce\Traits\SeedOnce;

class TestDataSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     *
     * @throws JsonException
     */
    public function run(): void
    {
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();

        /**
         * @var LogisticsZone $logisticsZone
         */
        $logisticsZone = LogisticsZone::all()->first();
        $country = Country::where('alpha2', 'nl')->first();
        $country->logistics_zone_id = $logisticsZone->id;
        $country->save();

        $state = State::create([
            'name' => 'Noord-Holland',
            'slug' => 'noord-holland',
            'short_name' => 'NH',
            'country_id' => $country->id,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        $city = City::create([
            'place_id' => 'ChIJVXealLU_xkcRja_At0z9AGY',
            'lat' => '52.3675734',
            'lng' => '4.9041389',
            'name' => 'Amsterdam',
            'slug' => 'amsterdam',
            'state_id' => $state->id,
            'country_id' => $country->id,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        $city2 = City::create([
            'place_id' => 'ChIJPa9QjMvxxUcRLjlgGEEmTt0',
            'lat' => '52.4569544',
            'lng' => '4.6060138',
            'name' => 'IJmuiden',
            'slug' => 'ijmuiden',
            'state_id' => $state->id,
            'country_id' => $country->id,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        // Test Manufacturer
        $testManufacturerUser = User::create([
            'username' => 'test_manufacturer',
            'name' => 'Test Manufacturer',
            'first_name' => 'Test',
            'last_name' => 'Manufacturer',
            'email' => 'test_manufacturer@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);
        $testManufacturerUser->assignRole('manufacturer');
        $manufacturer = Manufacturer::create([
            'user_id' => $testManufacturerUser->id,
            'country_id' => $country->id,
            'language_id' => 25,
            'currency_id' => 1,
            'name' => 'Test Manufacturer',
            'address_line1' => 'Teststraat 1',
            'postal_code' => '1111AA',
            'city_id' => $city->id,
            'state_id' => $state->id,
            'contact_name_1' => 'Tester',
            'phone_1' => '+31612345678',
            'email' => 'test_manufacturer@castimize.com',
            'billing_email' => 'test_manufacturer_billing@castimize.com',
            'coc_number' => '90001354',
            'vat_number' => '111234567B01',
            'iban' => 'NL98INGB0003856625',
            'bic' => 'INGB',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        // Test Customer
        $testCustomerUser = User::create([
            'username' => 'test_customer',
            'name' => 'Test Customer',
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test_customer@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);
        $testCustomerUser->assignRole('customer');
        $customer = Customer::create([
            'user_id' => $testCustomerUser->id,
            'country_id' => $country->id,
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'email' => 'test_customer@castimize.com',
            'phone' => '+31612345679',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        $address = Address::create([
            'place_id' => 'ChIJ-UMszDfwxUcR3p1C-yYTe08',
            'lat' => '52.4619168',
            'lng' => '4.6292418',
            'address_line1' => 'Willebrordstraat 80',
            'postal_code' => '1971DE',
            'city_id' => $city2->id,
            'state_id' => $state->id,
            'country_id' => $country->id,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        $pivotData = ['default_billing' => 1, 'default_shipping' => 1, 'contact_name' => 'Test contact'];
        $customer->addresses()->attach($address, $pivotData);

        $service = Service::create([
            'currency_id' => 1,
            'name' => 'Fast delivery',
            'fee' => 1000.00,
            'currency_code' => 'USD',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        $shippingFee = $logisticsZone->shippingFee()->create([
            'currency_id' => 1,
            'name' => 'UPS standard',
            'default_rate' => 2500.00,
            'currency_code' => 'USD',
            'default_lead_time' => 5,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);

        $complaintReason = ComplaintReason::create([
            'reason' => 'Niet volgens STL',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'created_by' => $systemUser->id,
        ]);
    }
}
