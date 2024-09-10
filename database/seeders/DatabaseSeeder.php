<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\Language;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::where('email', 'matthbon@hotmail.com')->first();
        if ($user === null) {
            $this->call(UserSeeder::class);
        }
        $role = Role::where('name', 'super-admin')->first();
        if ($role === null) {
            $this->call(RolesAndPermissionsSeeder::class);
        }
        $currency = Currency::where('code', 'EUR')->first();
        if ($currency === null) {
            $this->call(CurrencyTableSeeder::class);
        }
        $language = Language::where('iso', 'en')->first();
        if ($language === null) {
            $this->call(LanguagesTableSeeder::class);
        }
    }
}
