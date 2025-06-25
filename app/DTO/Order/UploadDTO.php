<?php

namespace App\DTO\Order;

use App\Models\Country;
use App\Models\Material;
use App\Models\Shop;
use Etsy\Resources\Receipt;

readonly class  UploadDTO
{
    public function __construct(
        public ?string $wpId,
        public ?int $materialId,
        public ?string $materialName,
        public string $name,
        public string $fileName,
        public float $modelVolumeCc,
        public float $modelXLength,
        public float $modelYLength,
        public float $modelZLength,
        public float $modelBoxVolume,
        public float $surfaceArea,
        public int $modelParts,
        public int $quantity,
        public float $subtotal,
        public float $subtotalTax,
        public float $total,
        public float $totalTax,
        public ?array $metaData,
        public int $customerLeadTime,
    ) {
    }

    public static function fromApiRequest()
    {

    }

    public static function fromWpRequest($lineItem, string $countryIso): UploadDTO
    {
        $country = Country::where('alpha2', $countryIso)->first();
        $name = null;
        $fileName = null;
        $material = null;
        $modelVolumeCc = null;
        $modelBoxVolume = null;
        $modelXLength = 0.01;
        $modelYLength = 0.01;
        $modelZLength = 0.01;
        $surfaceArea = null;
        $customerLeadTime = null;
        foreach ($lineItem->meta_data as $metaData) {
            if ($metaData->key === 'pa_p3d_filename') {
                $name = $metaData->value;
            }
            if ($metaData->key === 'pa_p3d_model') {
                $fileName = $metaData->value;
            }
            if ($metaData->key === 'pa_p3d_material') {
                [$materialId, $materialName] = array_pad(explode('. ', $metaData->value), 2, null);
                $material = Material::where('wp_id', $materialId)->first();
                $customerLeadTime = $material->dc_lead_time + ($country->logisticsZone->shippingFee?->default_lead_time ?? 0);
            }
            if ($metaData->key === '_p3d_stats_material_volume') {
                $modelVolumeCc = $metaData->value;
            }
            if ($metaData->key === '_p3d_stats_box_volume') {
                $modelBoxVolume = $metaData->value;
            }
            if ($metaData->key === '_p3d_stats_surface_area') {
                $surfaceArea = $metaData->value;
            }
        }

        return new self(
            wpId: $lineItem->id ?? null,
            materialId: $material?->id,
            materialName: $material?->name,
            name: $name,
            fileName: $fileName,
            modelVolumeCc: $modelVolumeCc,
            modelXLength: $modelXLength,
            modelYLength: $modelYLength,
            modelZLength: $modelZLength,
            modelBoxVolume: $modelBoxVolume,
            surfaceArea: $surfaceArea,
            modelParts: 1,
            quantity: $lineItem->quantity ?? 1,
            subtotal: $lineItem->subtotal,
            subtotalTax: $lineItem->subtotal_tax,
            total: $lineItem->total,
            totalTax: $lineItem->total_tax,
            metaData: $lineItem->meta_data,
            customerLeadTime: $customerLeadTime,
        );
    }

    public static function fromEtsyReceipt(Shop $shop, Receipt $receipt, $line, ?int $taxPercentage = null): UploadDTO
    {
        $country = Country::where('alpha2', $receipt->country_iso)->first();
        $model = $line['shop_listing_model']->model;
        $material = $model->materials->where('name', $line['material'])->first();

        $customerLeadTime = $material->dc_lead_time + ($country->logisticsZone->shippingFee?->default_lead_time ?? 0);

        $total = $line['transaction']->price->amount;
        $totalTax = 0;
        if ($taxPercentage) {
            $totalTax = ($taxPercentage / 100) * $total;
        }
        $metaDataWeight = $model->model_volume_cc * $material->density;
        $metaDataScale = sprintf('&times;%s (%s &times; %s &times; %s cm)', $model->model_scale, round($model->model_x_length, 2), round($model->model_y_length, 2), round($model->model_x_length, 2));

        $metaData = [
            [
                'key' => 'pa_p3d_printer',
                'value' => '3. Default',
            ],
            [
                'key' => 'pa_p3d_filename',
                'value' => $model->name,
            ],
            [
                'key' => 'pa_p3d_material',
                'value' => sprintf('%s. %s', $material->wp_id, $material->name),
            ],
            [
                'key' => 'pa_p3d_model',
                'value' => str_replace('wp-content/uploads/p3d/', '', $model->file_name),
            ],
            [
                'key' => 'pa_p3d_unit',
                'value' => 'mm',
            ],
            [
                'key' => 'pa_p3d_scale',
                'value' => $metaDataScale,
            ],
            [
                'key' => '_p3d_stats_material_volume',
                'value' => round($model->model_volume_cc, 2),
            ],
            [
                'key' => '_p3d_stats_print_time',
                'value' => '0',
            ],
            [
                'key' => '_p3d_stats_surface_area',
                'value' => round($model->model_surface_area_cm2, 2),
            ],
            [
                'key' => '_p3d_stats_weight',
                'value' => round($metaDataWeight, 2),
            ],
            [
                'key' => '_p3d_stats_box_volume',
                'value' => round($model->model_box_volume, 2),
            ],
        ];

        return new self(
            wpId: $model->wp_id ?? null,
            materialId: $material->id,
            materialName: $material->name,
            name: $model->name,
            fileName: $model->file_name,
            modelVolumeCc: $model->model_volume_cc,
            modelXLength: $model->model_x_length,
            modelYLength: $model->model_y_length,
            modelZLength: $model->model_z_length,
            modelBoxVolume: $model->model_box_volume,
            surfaceArea: $model->model_surface_area_cm2,
            modelParts: 1,
            quantity: $line['transaction']->quantity ?? 1,
            subtotal: $total / 100,
            subtotalTax: $totalTax / 100,
            total: $total / 100,
            totalTax: $totalTax / 100,
            metaData: $metaData,
            customerLeadTime: $customerLeadTime,
        );
    }
}
