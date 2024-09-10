<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'System User',
            'first_name' => 'System',
            'last_name' => 'User',
            'email' => 'matthijs.bon1@gmail.com',
            'password' => Hash::make(Str::random()),
        ]);
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();
        User::create([
            'name' => 'Matthijs Bon',
            'first_name' => 'Matthijs',
            'last_name' => 'Bon',
            'email' => 'matthbon@hotmail.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'avatar' => 'admin/users/IMCzoTYHnGl3xQwlNkcr4A9HNRE3MKP3r3ycTc2h.png',
            'created_by' => $systemUser->id,
        ]);
        User::create([
            'name' => 'Oscar Knoeff',
            'first_name' => 'Oscar',
            'last_name' => 'Knoeff',
            'email' => 'oscar@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_by' => $systemUser->id,
        ]);
        User::create([
            'name' => 'Robin Koonen',
            'first_name' => 'Robin',
            'last_name' => 'Koonen',
            'email' => 'robin@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_by' => $systemUser->id,
        ]);
        User::create([
            'name' => 'Koen Mennen',
            'first_name' => 'Koen',
            'last_name' => 'Mennen',
            'email' => 'koen@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_by' => $systemUser->id,
        ]);
    }
}
