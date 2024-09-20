<?php

namespace App\Services\Admin;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Customer;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Str;

class CustomersService
{
    /**
     * Store a customer completely from API request
     * @param $request
     * @return Customer
     */
    public function storeCustomerFromApi($request): Customer
    {
        $user = User::where('username', $request->username)->first();
        $user->first_name = $request->first_name;
        $user->last__name = $request->last_name;
        $user->save();
        $country = Country::where('alpha2', $request->billing['country'])->first();
        $countryShipping = Country::where('alpha2', $request->shipping['country'])->first();

        $customer = Customer::create([
            'user_id' => $user?->id,
            'country_id' => $country?->id,
            'wp_id' => $request->wp_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ]);

        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->billing['address_1'], $matchBilling);
        $billingStateName = $request->billing['state'];
        $stateBilling = null;
        if ($billingStateName) {
            $stateBilling = State::where('name', $billingStateName)->first();
        }
        $billingCityName = $request->billing['city'];
        $cityBilling = null;
        if ($billingCityName) {
            $cityBilling = City::firstOrCreate(
                ['name' => $billingCityName],
                ['name' => $billingCityName, 'slug' => Str::slug($billingCityName), 'state_id' => $stateBilling?->id]
            );
        }

        $billingAddress = Address::create([
            'address_line1' => $matchBilling[1] ?? null,
            'address_line2' => $request->billing['address_2'],
            'house_number' => $matchBilling[2] ?? null,
            'postal_code' => $request->billing['postcode'],
            'city_id' => $cityBilling?->id,
            'state_id' => $stateBilling?->id,
            'country_id' => $country?->id,
        ]);

        if ($request->shipping['address_1'] !== $request->billing['address_1']) {
            preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->shipping['address_1'], $matchShipping);
            $shippingStateName = $request->shipping['state'];
            $stateShipping = null;
            if ($shippingStateName) {
                $stateShipping = State::where('name', $shippingStateName)->first();
            }
            $shippingCityName = $request->shipping['city'];
            $cityShipping = null;
            if ($shippingCityName) {
                $cityShipping = City::firstOrCreate(
                    ['name' => $shippingCityName],
                    ['name' => $shippingCityName, 'slug' => Str::slug($shippingCityName), 'state_id' => $stateShipping?->id]
                );
            }
            $shippingAddress = Address::create([
                'address_line1' => $matchShipping[1] ?? null,
                'address_line2' => $request->shipping['address_2'],
                'house_number' => $matchShipping[2] ?? null,
                'postal_code' => $request->shipping['postcode'],
                'city_id' => $cityShipping?->id,
                'state_id' => $stateShipping?->id,
                'country_id' => $countryShipping?->id,
            ]);

            $pivotData = [
                'default_billing' => 1,
                'default_shipping' => 0,
                'contact_name' => sprintf('%s %s', $request->billing['first_name'], $request->billing['last_name']),
            ];
            $customer->addresses()->attach($billingAddress, $pivotData);

            $pivotData = [
                'default_billing' => 0,
                'default_shipping' => 1,
                'contact_name' => sprintf('%s %s', $request->shipping['first_name'], $request->shipping['last_name']),
            ];
            $customer->addresses()->attach($shippingAddress, $pivotData);
        } else {
            $pivotData = [
                'default_billing' => 1,
                'default_shipping' => 1,
                'contact_name' => sprintf('%s %s', $request->billing['first_name'], $request->billing['last_name']),
            ];
            $customer->addresses()->attach($billingAddress, $pivotData);
        }

        return $customer;
    }
}
