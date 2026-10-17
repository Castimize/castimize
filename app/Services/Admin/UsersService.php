<?php

namespace App\Services\Admin;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersService
{
    /**
     * Store a customer completely from API request
     */
    public function storeUserFromApi($request): Customer
    {
        $password = $request->wp_id ? $request->password : Hash::make($request->passsword);
        $user = User::where('wp_id', $request->wp_id)->first();
        if ($user === null) {
            $user = User::create([
                'wp_id' => $request->wp_id,
                'username' => $request->username ?? Str::slug($request->first_name.' '.$request->last_name).Str::random(4),
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => Str::random(10).'@castimize.com',
                'password' => $password,
            ]);
            $user->assignRole('customer');
        }

        return $user;
    }

    /**
     * Find or create the User account backing a Customer's own API access
     * (e.g. tokens used by the WordPress "My account" integration, or by a
     * customer using the API directly). Reuses the existing User<->wp_id
     * convention and 'customer' role rather than inventing a parallel
     * identity concept on the Customer model itself.
     */
    public function findOrCreateApiUser(Customer $customer): User
    {
        $user = User::firstOrCreate(
            ['wp_id' => $customer->wp_id],
            [
                'username' => Str::slug($customer->first_name.' '.$customer->last_name).'-'.$customer->wp_id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'email' => 'wp-'.$customer->wp_id.'@castimize.internal',
                'password' => Hash::make(Str::random(40)),
            ]
        );

        if (! $user->hasRole('customer')) {
            $user->assignRole('customer');
        }

        if ($customer->user_id !== $user->id) {
            $customer->update(['user_id' => $user->id]);
        }

        return $user;
    }
}
