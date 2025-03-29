<?php

namespace App\DTO\Model;

readonly class  ModelDTO
{
    public function __construct(
        public string $wpId,
        public ?int $customerId,
        public ?int $materialId,
        public ?int $printerId,
        public ?int $coatingId,
        public ?string $unit,
        public string $name,
        public ?string $modelName,
        public string $fileName,
        public ?string $thumbName,
        public float $modelVolumeCc,
        public float $modelXLength,
        public float $modelYLength,
        public float $modelZLength,
        public float $modelBoxVolume,
        public float $surfaceArea,
        public int $modelParts,
        public ?float $modelScale,
        public ?array $categories,
        public ?array $metaData,
    ) {
    }

    public static function fromApiRequest()
    {

    }

    public static function fromWpRequest($request, int $customerId)
    {
        $categories = null;
        if ($request->has('categories')) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }

        //printer_id.material_id.coating_id.scale.unit
        $thumbName = sprintf('%s%s%s%s%s%s.thumb.png',
            str_replace('_resized', '', $request->file_name),
            $request->printer_id ?? 3,
            $request->wp_id,
            $request->coating_id ?? null,
            $request->scale ?? 1,
            'mm',
        );

        return new self(
            wpId: (string) $request->wp_id,
            customerId: $customerId,
            materialId: null,
            printerId: $request->printer_id ?? 3,
            coatingId: $request->coating_id ?? null,
            unit: 'mm',
            name: $request->original_file_name,
            modelName: $request->model_name ?? null,
            fileName: $request->file_name,
            thumbName: $thumbName,
            modelVolumeCc: $request->material_volume,
            modelXLength: $request->x_dim,
            modelYLength: $request->y_dim,
            modelZLength: $request->z_dim,
            modelBoxVolume: $request->box_volume,
            surfaceArea: $request->surface_area,
            modelParts: $request->model_parts ?? 1,
            modelScale: $request->scale ?? 1,
            categories: $categories,
            metaData: $request->meta_data ?? null,
        );
    }
}
