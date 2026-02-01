<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use Ranium\SeedOnce\Traits\SeedOnce;

class CountriesTableSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     *
     * @throws JsonException
     */
    public function run(): void
    {
        $json = json_decode(file_get_contents(__DIR__.'/data/Countries.json'), true, 512, JSON_THROW_ON_ERROR);

        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();

        foreach ($json as $country) {
            DB::table('countries')
                ->insert([
                    'name' => $country['name'],
                    'alpha2' => $country['alpha2'],
                    'alpha3' => $country['alpha3'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $systemUser->id,
                ]);
        }
    }
}
