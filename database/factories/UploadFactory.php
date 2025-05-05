<?php

namespace Database\Factories;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Order;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Upload>
 */
class UploadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = fake()->numberBetween(1001, 10000);
        $modelVolumeCc = fake()->randomFloat(5, 0.12, 100.00);
        $modelXLength = fake()->randomFloat(5, 0.12, 100.00);
        $modelYLength = fake()->randomFloat(5, 0.12, 100.00);
        $modelZLength = fake()->randomFloat(5, 0.12, 100.00);
        $modelSurfaceArea = fake()->randomFloat(5, 0.12, 100.00);
        $modelBoxVolume = fake()->randomFloat(5, 0.12, 100.00);

        $metaDataWeight = $modelVolumeCc * 8.5;
        $metaDataScale = sprintf('&times;%s (%s &times; %s &times; %s cm)', 1, round($modelXLength, 2), round($modelYLength, 2), round($modelZLength, 2));

        return [
            'order_id' => Order::factory(),
            'material_id' => Material::factory(),
            'customer_id' => Customer::factory(),
            'currency_id' => Currency::factory(),
            'name' => fake()->name,
            'file_name' => 'test.stl',
            'material_name' => fake()->name,
            'model_volume_cc' => $modelVolumeCc,
            'model_x_length' => $modelXLength,
            'model_y_length' => $modelYLength,
            'model_z_length' => $modelZLength,
            'model_surface_area_cm2' => $modelSurfaceArea,
            'model_parts' => 1,
            'model_box_volume' => $modelBoxVolume,
            'quantity' => fake()->numberBetween(1, 3),
            'subtotal' => $total,
            'subtotal_tax' => 0,
            'total' => $total,
            'total_tax' => 0,
            'currency_code' => fake()->currencyCode(),
            'customer_lead_time' => fake()->numberBetween(10, 20),
            'meta_data' => [
                [
                    'key' => 'pa_p3d_printer',
                    'value' => '3. Default',
                    'display_key' => 'Printer',
                    'display_value' => '3. Default',
                ],
                [
                    'key' => 'pa_p3d_filename',
                    'value' => 'test.stl',
                    'display_key' => 'Filename',
                    'display_value' => 'test.stl',
                ],
                [
                    'key' => 'pa_p3d_material',
                    'value' => '1. Name',
                    'display_key' => 'Material',
                    'display_value' => '1. Name',
                ],
                [
                    'key' => 'pa_p3d_model',
                    'value' => 'test_model.stl',
                    'display_key' => 'Model',
                    'display_value' => 'test_model.stl',
                ],
                [
                    'key' => 'pa_p3d_unit',
                    'value' => 'mm',
                    'display_key' => 'Unit',
                    'display_value' => 'mm',
                ],
                [
                    'key' => 'pa_p3d_scale',
                    'value' => $metaDataScale,
                    'display_key' => 'Scale',
                    'display_value' => $metaDataScale,
                ],
                [
                    'key' => '_p3d_stats_material_volume',
                    'value' => round($modelVolumeCc, 2),
                    'display_key' => 'Material Volume',
                    'display_value' => round($modelVolumeCc, 2) . 'cm3',
                ],
                [
                    'key' => '_p3d_stats_print_time',
                    'value' => '0',
                    'display_key' => 'Print Time',
                    'display_value' => '00:00:00',
                ],
                [
                    'key' => '_p3d_stats_surface_area',
                    'value' => round($modelSurfaceArea, 2),
                    'display_key' => 'Surface Area',
                    'display_value' => round($modelSurfaceArea, 2) . 'cm2',
                ],
                [
                    'key' => '_p3d_stats_weight',
                    'value' => round($metaDataWeight, 2),
                    'display_key' => 'Model Weight',
                    'display_value' => round($metaDataWeight, 2) . 'g',
                ],
                [
                    'key' => '_p3d_stats_box_volume',
                    'value' => round($modelBoxVolume, 2),
                    'display_key' => 'Box Volume',
                    'display_value' => round($modelBoxVolume, 2) . 'cm3',
                ],
            ],
        ];
    }
}
