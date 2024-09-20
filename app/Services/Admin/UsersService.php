<?php

namespace App\Services\Admin;

use App\Models\Address;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersService
{
    /**
     * Store a customer completely from API request
     * @param $request
     * @return Customer
     */
    public function storeUserFromApi($request): Customer
    {
        $password = $request->wp_id ? $request->password : Hash::make($request->passsword);
        $user = User::create([
            'wp_id' => $request->wp_id,
            'username' => $request->username,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $password,
        ]);
        $user->assignRole('customer');

        return $user;
    }
}
