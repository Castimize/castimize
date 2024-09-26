<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Ranium\SeedOnce\Traits\SeedOnce;

class DatabaseSeeder extends Seeder
{
    use SeedOnce;
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(CurrencyTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(LogisticsZonesTableSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(OrderStatusSeeder::class);

        if (env('APP_ENV') === 'local') {
            $this->call(TestDataSeeder::class);
        }
    }
}
