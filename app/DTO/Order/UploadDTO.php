<?php

namespace App\DTO\Order;

use App\Models\Material;

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

    public static function fromWpRequest($lineItem)
    {
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
                [$materialId, $materialName] = explode('. ', $metaData->value);
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
}
