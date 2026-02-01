<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use Ranium\SeedOnce\Traits\SeedOnce;

class LogisticsZonesTableSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     *
     * @throws JsonException
     */
    public function run(): void
    {
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();

        for ($i = 1; $i < 10; $i++) {
            DB::table('logistics_zones')
                ->insert([
                    'name' => $i,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $systemUser->id,
                ]);
        }
    }
}
