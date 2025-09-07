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
}
