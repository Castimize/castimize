<?php

namespace App\Services\Admin;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;

class CustomersService
{
    /**
     * Store a customer completely from API request
     * @param $request
     * @return Customer
     */
    public function storeCustomerFromApi($request): Customer
    {
        $user = User::create([]);
        $user->assignRole('customer');

        $customer = Customer::create([]);

//        $address = Address::create([
//            'place_id' => 'ChIJ-UMszDfwxUcR3p1C-yYTe08',
//            'lat' => '52.4619168',
//            'lng' => '4.6292418',
//            'address_line1' => 'Willebrordstraat',
//            'house_number' => '80',
//            'postal_code' => '1971DE',
//            'city_id' => $city2->id,
//            'state_id' => $state->id,
//            'country_id' => $country->id,
//            'created_at' => now()->format('Y-m-d H:i:s'),
//            'created_by' => $systemUser->id,
//        ]);

        $pivotData = [ 'default_billing' => 1, 'default_shipping' => 1, 'contact_name' => 'Test contact' ];
//        $customer->addresses()->attach($address, $pivotData);

        return $customer;
    }
}
