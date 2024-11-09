<?php

namespace App\Services\Admin;

use App\Models\Address;
use App\Models\City;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\State;
use App\Models\User;
use Illuminate\Support\Str;

class CustomersService
{
    public function storeCustomerFromWpCustomer($wpCustomer)
    {
        $customer = Customer::where('wp_id', $wpCustomer['id'])->first();

        if ($customer === null && !empty($wpCustomer['email'])) {
            $customer = Customer::where('email', $wpCustomer['email'])->first();
        }

        if ($customer === null) {
            return $this->createCustomerFromWpCustomer($wpCustomer);
        }
        return $this->updateCustomerFromWpCustomer($customer, $wpCustomer);
    }

    public function storeCustomerFromWpApi($request): Customer
    {
        $customer = null;
        if ($request->has('id') && !empty($request->id)) {
            $customer = Customer::where('wp_id', $request->id)->first();
        }
        if ($customer === null && $request->has('email') && !empty($request->email)) {
            $customer = Customer::where('email', $request->email)->first();
        }

        if ($customer === null) {
            return $this->createCustomerFromWp($request);
        }
        return $this->updateCustomerFromWp($customer, $request);
    }

    private function createCustomerFromWp($request): mixed
    {
        $country = Country::where('alpha2', strtolower($request->billing['country']))->first();
        if ($country === null) {
            $country = Country::where('alpha2', 'nl')->first();
        }
        $currency = Currency::where('code', $request->currency)->first();

        $vatNumber = null;
        if ($request->has('meta_data')) {
            foreach ($request->meta_data as $orderMetaData) {
                if ($orderMetaData['key'] === '_billing_eu_vat_number') {
                    $vatNumber = $orderMetaData['value'];
                }
            }
        }

        $customer = Customer::create([
            'country_id' => $country->id,
            'currency_id' => $currency?->id,
            'wp_id' => $request->id,
            'first_name' => $request->first_name ?? $request->billing['first_name'],
            'last_name' => $request->last_name ?? $request->billing['last_name'],
            'company' => $request->billing['company'] ?? $request->shipping['company'] ?? null,
            'email' => $request->email ?? $request->billing['email'] ?? null,
            'phone' => $request->phone ?? $request->billing['phone'] ?? null,
            'vat_number' => $vatNumber,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $billingAddress = $this->createAddress($request->billing);

        if ($request->shipping['address_1'] !== $request->billing['address_1']) {
            $shippingAddress = $this->createAddress($request->shipping);

            if ($shippingAddress !== null) {
                $pivotData = [
                    'default_billing' => 1,
                    'default_shipping' => 0,
                    'contact_name' => sprintf('%s %s', $request->billing['first_name'], $request->billing['last_name']),
                    'phone' => $request->billing['phone'] ?? null,
                ];
                $customer->addresses()->attach($billingAddress, $pivotData);

                $pivotData = [
                    'default_billing' => 0,
                    'default_shipping' => 1,
                    'contact_name' => sprintf('%s %s', $request->shipping['first_name'], $request->shipping['last_name']),
                    'phone' => $request->shiping['phone'] ?? null,
                ];
                $customer->addresses()->attach($shippingAddress, $pivotData);
            }
        } else if ($billingAddress !== null) {
            $pivotData = [
                'default_billing' => 1,
                'default_shipping' => 1,
                'contact_name' => sprintf('%s %s', $request->billing['first_name'], $request->billing['last_name']),
                'phone' => $request->billing['phone'] ?? null,
            ];
            $customer->addresses()->attach($billingAddress, $pivotData);
        }

        return $customer;
    }

    private function createCustomerFromWpCustomer($wpCustomer): mixed
    {
        $country = Country::where('alpha2', strtolower($wpCustomer['billing']->country))->first();
        if ($country === null) {
            $country = Country::where('alpha2', 'nl')->first();
        }

        $vatNumber = null;
        foreach ($wpCustomer['meta_data'] as $metaData) {
            if ($metaData->key === 'billing_eu_vat_number' && !empty($metaData->value)) {
                $vatNumber = $metaData->value;
            }
        }

        $customer = Customer::create([
            'country_id' => $country->id,
            'wp_id' => $wpCustomer['id'],
            'first_name' => $wpCustomer['first_name'] ?? $wpCustomer['billing']->first_name,
            'last_name' => $wpCustomer['last_name'] ?? $wpCustomer['billing']->last_name,
            'company' => $wpCustomer['billing']->company ?? $wpCustomer['shipping']->company ?? null,
            'email' => $wpCustomer['email'] ?? $wpCustomer['billing']->email ?? null,
            'phone' => $wpCustomer['phone'] ?? $wpCustomer['billing']->phone ?? null,
            'vat_number' => $vatNumber,
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $billingAddress = $this->createAddressFromWpCustomer($wpCustomer['billing']);

        if ($wpCustomer['shipping']->address_1 !== $wpCustomer['billing']->address_1) {
            $shippingAddress = $this->createAddressFromWpCustomer($wpCustomer['shipping']);

            if ($shippingAddress !== null) {
                $pivotData = [
                    'default_billing' => 1,
                    'default_shipping' => 0,
                    'contact_name' => sprintf('%s %s', $wpCustomer['billing']->first_name, $wpCustomer['billing']->last_name),
                    'phone' => $wpCustomer['billing']->phone ?? null,
                    'email' => $wpCustomer['billing']->email ?? null,
                ];
                $customer->addresses()->attach($billingAddress, $pivotData);

                $pivotData = [
                    'default_billing' => 0,
                    'default_shipping' => 1,
                    'contact_name' => sprintf('%s %s', $wpCustomer['shipping']->first_name, $wpCustomer['shipping']->last_name),
                    'phone' => $wpCustomer['shiping']->phone ?? null,
                ];
                $customer->addresses()->attach($shippingAddress, $pivotData);
            }
        } else if ($billingAddress !== null) {
            $pivotData = [
                'default_billing' => 1,
                'default_shipping' => 1,
                'contact_name' => sprintf('%s %s', $wpCustomer['billing']->first_name, $wpCustomer['billing']->last_name),
                'phone' => $wpCustomer['billing']->phone ?? null,
                'email' => $wpCustomer['billing']->email ?? null,
            ];
            $customer->addresses()->attach($billingAddress, $pivotData);
        }

        return $customer;
    }

    private function updateCustomerFromWpCustomer(Customer $customer, $wpCustomer): mixed
    {
        $customer->wp_id = $wpCustomer['id'];
        $customer->first_name = $wpCustomer['first_name'] ?? $wpCustomer['billing']->first_name;
        $customer->last_name = $wpCustomer['last_name'] ?? $wpCustomer['billing']->last_name;
        $customer->email = $wpCustomer['email'] ?? $wpCustomer['billing']->email ?? null;
        $customer->save();

        $billingAddress = null;
        $shippingAddress = null;
//        if ($wpCustomer['billing']->postcode !== null) {
//            $billingAddress = $customer->addresses->where('postal_code', $wpCustomer['billing']->postcode)->where('address_line1', $wpCustomer['billing']->address_1 ?? '-')->first();
//        }
//        if ($wpCustomer['shipping']->postcode !== null) {
//            $shippingAddress = $customer->addresses->where('postal_code', $wpCustomer['shipping']->postcode)->where('address_line1', $wpCustomer['shipping']->address_1 ?? '-')->first();
//        }
//        if ($billingAddress === null) {
//            $billingAddress = $this->createAddressFromWpCustomer($wpCustomer['billing']);
//            if ($billingAddress !== null) {
//                $customer->addresses()->wherePivot('default_billing', 1)->update(['default_billing' => 0]);
//                $pivotData = [
//                    'default_billing' => 1,
//                    'default_shipping' => 0,
//                    'contact_name' => sprintf('%s %s', $wpCustomer['billing']->first_name, $wpCustomer['billing']->last_name),
//                    'phone' => $wpCustomer['billing']->phone ?? null,
//                    'email' => $wpCustomer['billing']->email ?? null,
//                ];
//                $customer->addresses()->attach($billingAddress, $pivotData);
//            }
//        }
//
//        if ($shippingAddress === null || $shippingAddress->address_line1 !== $billingAddress->address_line1) {
//            if ($shippingAddress === null) {
//                $shippingAddress = $this->createAddressFromWpCustomer($wpCustomer['shipping']);
//                if ($shippingAddress !== null) {
//                    $customer->addresses()->wherePivot('default_shipping', 1)->update(['default_shipping' => 0]);
//                    $pivotData = [
//                        'default_billing' => 0,
//                        'default_shipping' => 1,
//                        'contact_name' => sprintf('%s %s', $wpCustomer['shipping']->first_name, $wpCustomer['shipping']->last_name),
//                        'phone' => $wpCustomer['shipping']->phone ?? null,
//                    ];
//                    $customer->addresses()->attach($shippingAddress, $pivotData);
//                }
//            }
//        } else if ($billingAddress !== null) {
//            $pivotData = [
//                'default_billing' => 1,
//                'default_shipping' => 1,
//                'contact_name' => sprintf('%s %s', $wpCustomer['billing']->first_name, $wpCustomer['billing']->last_name),
//                'phone' => $wpCustomer['billing']->phone ?? null,
//                'email' => $wpCustomer['billing']->email ?? null,
//            ];
//            $customer->addresses()->attach($billingAddress, $pivotData);
//        }

        return $customer;
    }

    private function updateCustomerFromWp(Customer $customer, $request): mixed
    {
        $customer->wp_id = $request->id;
        $customer->first_name = $request->first_name ?? $request->billing['first_name'];
        $customer->last_name = $request->last_name ?? $request->billing['last_name'];
        $customer->email = $request->email ?? $request->billing['email'] ?? null;
        $customer->save();

        $billingAddress = null;
        $shippingAddress = null;
        if ($request->billing['postcode'] !== null) {
            $billingAddress = $customer->addresses->where('postal_code', $request->billing['postcode'])->where('address_line1', $request->billing['address_1'] ?? '-')->first();
        }
        if ($request->shipping['postcode'] !== null) {
            $shippingAddress = $customer->addresses->where('postal_code', $request->shipping['postcode'])->where('address_line1', $request->shipping['address_1'] ?? '-')->first();
        }
        if ($billingAddress === null) {
            $billingAddress = $this->createAddress($request->billing);
            if ($billingAddress !== null) {
                $customer->addresses()->wherePivot('default_billing', 1)->update(['default_billing' => 0]);
            }
        }

        if ($shippingAddress === null || $shippingAddress->address_line1 !== $billingAddress->address_line1) {
            $shippingAddress = $this->createAddress($request->shipping);
            if ($shippingAddress !== null) {
                $customer->addresses()->wherePivot('default_shipping', 1)->update(['default_shipping' => 0]);
                $pivotData = [
                    'default_billing' => 1,
                    'default_shipping' => 0,
                    'contact_name' => sprintf('%s %s', $request->billing['first_name'], $request->billing['last_name']),
                    'phone' => $request->billing['phone'] ?? null,
                ];
                $customer->addresses()->attach($billingAddress, $pivotData);

                $pivotData = [
                    'default_billing' => 0,
                    'default_shipping' => 1,
                    'contact_name' => sprintf('%s %s', $request->shipping['first_name'], $request->shipping['last_name']),
                    'phone' => $request->shipping['phone'] ?? null,
                ];
                $customer->addresses()->attach($shippingAddress, $pivotData);
            }
        } else if ($billingAddress !== null) {
            $pivotData = [
                'default_billing' => 1,
                'default_shipping' => 1,
                'contact_name' => sprintf('%s %s', $request->billing['first_name'], $request->billing['last_name']),
                'phone' => $request->billing['phone'] ?? null,
            ];
            $customer->addresses()->attach($billingAddress, $pivotData);
        }

        return $customer;
    }

    private function createAddressFromWpCustomer($input)
    {
        if ($input->postcode !== null) {
            $country = Country::where('alpha2', strtolower($input->country))->first();
            if ($country === null) {
                $country = Country::where('alpha2', 'nl')->first();
            }

            $stateName = $input->state;
            $state = null;
            if ($stateName) {
                $state = State::firstOrCreate(
                    ['name' => $stateName],
                    ['name' => $stateName, 'slug' => Str::slug($stateName), 'country_id' => $country->id]
                );
            }
            $cityName = $input->city;
            $city = null;
            if ($cityName) {
                $city = City::firstOrCreate(
                    ['name' => $cityName],
                    ['name' => $cityName, 'slug' => Str::slug($cityName), 'state_id' => $state?->id, 'country_id' => $country->id]
                );
            }

            $address = Address::where('postal_code', $input->postcode)->where('address_line1', $input->address_1 ?? '-')->first();
            if ($address === null) {
                $address = Address::create([
                    'address_line1' => $input->address_1,
                    'address_line2' => $input->address_2 ?? null,
                    'postal_code' => $input->postcode,
                    'city_id' => $city?->id,
                    'state_id' => $state?->id,
                    'country_id' => $country->id,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            return $address;
        }
        return null;
    }

    private function createAddress(array $input)
    {
        if ($input['postcode'] !== null) {
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

            $address = Address::where('postal_code', $input['postcode'])->where('address_line1', $input['address_1'] ?? '-')->first();
            if ($address === null) {
                $address = Address::create([
                    'address_line1' => $input['address_1'],
                    'address_line2' => $input['address_2'] ?? null,
                    'postal_code' => $input['postcode'],
                    'city_id' => $city?->id,
                    'state_id' => $state?->id,
                    'country_id' => $country->id,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            return $address;
        }
        return null;
    }
}
