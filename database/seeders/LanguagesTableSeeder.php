<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;

class LanguagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * @throws JsonException
     */
    public function run(): void
    {
        $json = json_decode(file_get_contents(__DIR__ . '/data/Languages.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach ($json as $language) {
            DB::table('languages')
                ->insert([
                    'iso' => $language['iso'],
                    'locale' => $language['locale'],
                    'local_name' => $language['local_name'],
                    'en_name' => $language['en_name'],
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
        }
    }
}
