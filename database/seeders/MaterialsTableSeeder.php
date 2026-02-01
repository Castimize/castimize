<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use JsonException;
use Ranium\SeedOnce\Traits\SeedOnce;

class MaterialsTableSeeder extends Seeder
{
    use SeedOnce;

    /**
     * Run the database seeds.
     *
     * @throws JsonException
     */
    public function run(): void
    {
        $json = json_decode(file_get_contents(__DIR__.'/data/MaterialGroups.json'), true, 512, JSON_THROW_ON_ERROR);
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();

        foreach ($json as $materialGroups) {
            DB::table('material_groups')
                ->insert([
                    'name' => $materialGroups['name'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $systemUser->id,
                ]);
        }

        $json = json_decode(file_get_contents(__DIR__.'/data/Materials.json'), true, 512, JSON_THROW_ON_ERROR);
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();

        foreach ($json as $material) {
            DB::table('materials')
                ->insert([
                    'id' => $material['id'],
                    'material_group_id' => $material['material_group_id'],
                    'currency_id' => $material['currency_id'],
                    'wp_id' => $material['wp_id'],
                    'name' => $material['name'],
                    'discount' => $material['discount'],
                    'bulk_discount_10' => $material['bulk_discount_10'],
                    'bulk_discount_25' => $material['bulk_discount_25'],
                    'bulk_discount_50' => $material['bulk_discount_50'],
                    'dc_lead_time' => $material['dc_lead_time'],
                    'fast_delivery_lead_time' => $material['fast_delivery_lead_time'],
                    'fast_delivery_fee' => $material['fast_delivery_fee'],
                    'currency_code' => $material['currency_code'],
                    'hs_code_description' => $material['hs_code_description'],
                    'hs_code' => $material['hs_code'],
                    'article_eu_description' => $material['article_eu_description'],
                    'article_us_description' => $material['article_us_description'],
                    'tariff_code_eu' => $material['tariff_code_eu'],
                    'tariff_code_us' => $material['tariff_code_us'],
                    'minimum_x_length' => $material['minimum_x_length'],
                    'maximum_x_length' => $material['maximum_x_length'],
                    'minimum_y_length' => $material['minimum_y_length'],
                    'maximum_y_length' => $material['maximum_y_length'],
                    'minimum_z_length' => $material['minimum_z_length'],
                    'maximum_z_length' => $material['maximum_z_length'],
                    'minimum_volume' => $material['minimum_volume'],
                    'maximum_volume' => $material['maximum_volume'],
                    'minimum_box_volume' => $material['minimum_box_volume'],
                    'maximum_box_volume' => $material['maximum_box_volume'],
                    'density' => $material['density'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $systemUser->id,
                ]);
        }

        $json = json_decode(file_get_contents(__DIR__.'/data/Prices.json'), true, 512, JSON_THROW_ON_ERROR);
        $systemUser = User::where('email', 'matthijs.bon1@gmail.com')->first();

        foreach ($json as $price) {
            DB::table('prices')
                ->insert([
                    'id' => $price['id'],
                    'material_id' => $price['material_id'],
                    'country_id' => $price['country_id'],
                    'currency_id' => $price['currency_id'],
                    'setup_fee' => $price['setup_fee'],
                    'setup_fee_amount' => $price['setup_fee_amount'],
                    'minimum_per_stl' => $price['minimum_per_stl'],
                    'price_minimum_per_stl' => $price['price_minimum_per_stl'],
                    'price_volume_cc' => $price['price_volume_cc'],
                    'price_surface_cm2' => $price['price_surface_cm2'],
                    'fixed_fee_per_part' => $price['fixed_fee_per_part'],
                    'material_discount' => $price['material_discount'],
                    'bulk_discount' => $price['bulk_discount'],
                    'currency_code' => $price['currency_code'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $systemUser->id,
                ]);
        }
    }
}
