<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Ranium\SeedOnce\Traits\SeedOnce;

class UserSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'username' => 'system',
            'name' => 'System User',
            'first_name' => 'System',
            'last_name' => 'User',
            'email' => 'matthijs.bon1@gmail.com',
            'password' => Hash::make(Str::random()),
        ]);
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();
        $user = User::create([
            'username' => 'matthbon',
            'name' => 'Matthijs Bon',
            'first_name' => 'Matthijs',
            'last_name' => 'Bon',
            'email' => 'matthbon@hotmail.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'avatar' => 'admin/users/IMCzoTYHnGl3xQwlNkcr4A9HNRE3MKP3r3ycTc2h.png',
            'created_by' => $systemUser->id,
        ]);
        $token = $user->createToken('test-token', ['*'])->plainTextToken;
        $this->command->info('Test token for user: '.$user->name.' => '.$token);
        User::create([
            'username' => 'oknoeff',
            'name' => 'Oscar Knoeff',
            'first_name' => 'Oscar',
            'last_name' => 'Knoeff',
            'email' => 'oscar@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_by' => $systemUser->id,
        ]);
        User::create([
            'username' => 'rkoonen',
            'name' => 'Robin Koonen',
            'first_name' => 'Robin',
            'last_name' => 'Koonen',
            'email' => 'robin@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_by' => $systemUser->id,
        ]);
        User::create([
            'username' => 'kmennen',
            'name' => 'Koen Mennen',
            'first_name' => 'Koen',
            'last_name' => 'Mennen',
            'email' => 'koen@castimize.com',
            'password' => Hash::make('1Qaz2Wsx!'),
            'created_by' => $systemUser->id,
        ]);
    }
}
