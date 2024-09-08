<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Matthijs Bon',
            'first_name' => 'Matthijs',
            'last_name' => 'Bon',
            'email' => 'matthbon@hotmail.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
        User::create([
            'name' => 'Oscar Knoeff',
            'first_name' => 'Oscar',
            'last_name' => 'Knoeff',
            'email' => 'oscar@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
        User::create([
            'name' => 'Robin Koonen',
            'first_name' => 'Robin',
            'last_name' => 'Koonen',
            'email' => 'robin@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
        User::create([
            'name' => 'Koen Mennen',
            'first_name' => 'Koen',
            'last_name' => 'Mennen',
            'email' => 'koen@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }
}
