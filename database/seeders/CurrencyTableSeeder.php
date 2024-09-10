<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws JsonException
     */
    public function run(): void
    {
        $json = json_decode(file_get_contents(__DIR__ . '/data/Currencies.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach ($json as $currency) {
            DB::table('currencies')
                ->insert([
                    'name' => $currency['name'],
                    'code' => $currency['code'],
                    'symbol' => $currency['symbol'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }
}
