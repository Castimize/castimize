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
    public function storeCustomerFromWpApi($request): Customer
    {
        $customer = null;
        if ($request->has('customer_id') && !empty($request->customer_id)) {
            $customer = Customer::where('wp_id', $request->customer_id)->first();
        }

        if ($customer === null) {
            return $this->createCustomerFromWp($request);
        }
        return $this->updateCustomerFromWp($customer, $request);
    }

    /**
     * @param $request
     * @return mixed
     */
    private function createCustomerFromWp($request): mixed
    {
        $country = Country::where('alpha2', strtolower($request->billing['country']))->first();
        if ($country === null) {
            $country = Country::where('alpha2', 'nl')->first();
        }

        $customer = Customer::create([
            'country_id' => $country->id,
            'wp_id' => $request->customer_id,
            'first_name' => $request->first_name ?? $request->billing['first_name'],
            'last_name' => $request->last_name ?? $request->billing['last_name'],
            'email' => $request->email ?? $request->billing['email'],
        ]);

        $billingAddress = $this->createAddress($request->billing);

        if ($request->shipping['address_1'] !== $billingAddress->address_line1) {
            $shippingAddress = $this->createAddress($request->shipping);

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

    /**
     * @param $request
     * @return mixed
     */
    private function updateCustomerFromWp(Customer $customer, $request): mixed
    {
        $customer->first_name = $request->first_name ?? $request->billing['first_name'];
        $customer->last_name = $request->last_name ?? $request->billing['last_name'];
        $customer->email = $request->email ?? $request->billing['email'];
        $customer->save();

        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->billing['address_1'], $matchBilling);
        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->shipping['address_1'], $matchShipping);

        $billingAddress = $customer->addresses->where('postal_code', $request->billing['postcode'])->where('house_number', $matchBilling[2] ?? '-')->first();
        $shippingAddress = $customer->addresses->where('postal_code', $request->shipping['postcode'])->where('house_number', $matchShipping[2] ?? '-')->first();
        if ($billingAddress === null) {
            $billingAddress = $this->createAddress($request->billing);
            $customer->addresses()->wherePivot('default_billing', 1)->update(['default_billing' => 0]);
        }

        if ($shippingAddress === null || $shippingAddress->address_line1 !== $billingAddress->address_line1) {
            $shippingAddress = $this->createAddress($request->shipping);
            $customer->addresses()->wherePivot('default_shipping', 1)->update(['default_shipping' => 0]);
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

    private function createAddress(array $input)
    {
        $country = Country::where('alpha2', strtolower($input['country']))->first();
        if ($country === null) {
            $country = Country::where('alpha2', 'nl')->first();
        }

        $stateName = $input['state'];
        $state = null;
        if ($stateName) {
            $state = State::firstOrCreate(
                ['name' => $stateName],
                ['name' => $stateName, 'slug' => Str::slug($stateName), 'country_id' => $country->id]
            );
        }
        $cityName = $input['city'];
        $city = null;
        if ($cityName) {
            $city = City::firstOrCreate(
                ['name' => $cityName],
                ['name' => $cityName, 'slug' => Str::slug($cityName), 'state_id' => $state?->id, 'country_id' => $country->id]
            );
        }

        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $input['address_1'], $match);

        $address = Address::where('postal_code', $input['postcode'])->where('house_number', $match[2] ?? '-')->first();
        if ($address === null) {
            $address = Address::create([
                'address_line1' => $match[1] ?? null,
                'address_line2' => $input['address_2'],
                'house_number' => $match[2] ?? null,
                'postal_code' => $input['postcode'],
                'city_id' => $city?->id,
                'state_id' => $state?->id,
                'country_id' => $country->id,
            ]);
        }

        return $address;
    }
}
