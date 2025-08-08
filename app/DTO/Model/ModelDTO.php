<?php

namespace App\DTO\Model;

use App\Models\Material;
use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

readonly class ModelDTO
{
    public function __construct(
        public string $wpId,
        public ?int $customerId,
        public ?int $shopListingId,
        public ?int $shopTaxonomyId,
        public array $materials,
        public ?int $printerId,
        public ?int $coatingId,
        public ?string $unit,
        public string $name,
        public ?string $modelName,
        public string $fileName,
        public ?string $thumbName,
        public bool $uploadedThumb,
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

    public static function fromWpRequest(Request $request, int $customerId): ModelDTO
    {
        $material = Material::where('wp_id', $request->wp_id)->first();

        $categories = null;
        if ($request->categories) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }

        if ($request->hasFile('thumb_image')) {
            $uploadedThumb = true;
            $file = $request->file('thumb_image');
            $thumbFileName = $file->getClientOriginalName();
            $thumbFileNameWithoutExt = pathinfo($thumbFileName, PATHINFO_FILENAME);
            $thumbFileExtension = $file->getClientOriginalExtension();
            $thumbName = time().'_'.str_replace(' ','_', $thumbFileNameWithoutExt) . '.' . $thumbFileExtension;
            Storage::disk('r2')->putFileAs(env('APP_SITE_STL_UPLOAD_DIR'), $file, $thumbName);
        } else {
            $uploadedThumb = false;
            //printer_id.material_id.coating_id.scale.unit
            $thumbName = sprintf('%s_%s%s%s%s%s.thumb.png',
                str_replace('_resized', '', $request->file_name),
                $request->printer_id ?? 3,
                $request->wp_id ?? 1,
                $request->coating_id ?? null,
                1,
                'mm',
            );

            $fileNameThumb = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $thumbName);
            $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
            $fileHeaders = get_headers($fileThumb);
            if (str_contains($fileHeaders[0], '404')) {
                $thumbName = sprintf('%s_%s%s%s%s%s.thumb.png',
                    str_replace('_resized', '', $request->file_name),
                    $request->printer_id ?? 3,
                    1,
                    $request->coating_id ?? null,
                    1,
                    'mm',
                );

                $fileNameThumb = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $thumbName);
                $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
                $fileHeaders = get_headers($fileThumb);
                if (str_contains($fileHeaders[0], '404')) {
                    $model = Model::where('file_name', 'like', '%' . str_replace('_resized', '', $request->file_name) . '%')->first();
                    if ($model) {
                        $thumbName = str_replace(env('APP_SITE_STL_UPLOAD_DIR'), '', $model->thumb_name);
                    }
                }
            }
        }

        return new self(
            wpId: (string) $request->wp_id,
            customerId: $customerId,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [$material],
            printerId: $request->printer_id ?? 3,
            coatingId: $request->coating_id ?? null,
            unit: 'mm',
            name: $request->original_file_name ?? $request->file_name ?? '',
            modelName: $request->model_name ?? null,
            fileName: $request->file_name,
            thumbName: $thumbName,
            uploadedThumb: $uploadedThumb,
            modelVolumeCc: $request->material_volume,
            modelXLength: $request->x_dim,
            modelYLength: $request->y_dim,
            modelZLength: $request->z_dim,
            modelBoxVolume: $request->box_volume,
            surfaceArea: $request->surface_area,
            modelParts: $request->model_parts ?? 1,
            modelScale: $request->scale ? number_format(round((float) $request->scale, 4), 4) : 1,
            categories: $categories,
            metaData: $request->meta_data ?? null,
        );
    }

    public static function fromWpUpdateRequest(Request $request, Model $model, int $customerId): ModelDTO
    {
        $categories = null;
        if ($request->categories) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }

        $uploadedThumb = false;
        if ($request->hasFile('thumb_image')) {
            $uploadedThumb = true;
            $file = $request->file('thumb_image');
            $thumbFileName = $file->getClientOriginalName();
            $thumbFileNameWithoutExt = pathinfo($thumbFileName, PATHINFO_FILENAME);
            $thumbFileExtension = $file->getClientOriginalExtension();
            $thumbName = time().'_'.str_replace(' ','_', $thumbFileNameWithoutExt) . '.' . $thumbFileExtension;
            Storage::disk('r2')->putFileAs(env('APP_SITE_STL_UPLOAD_DIR'), $file, $thumbName);
        }

        return new self(
            wpId: (string) $model->materials->first()->wp_id,
            customerId: $customerId,
            shopListingId: $request->shop_listing_id ?? null,
            shopTaxonomyId: $request->shop_taxonomy_id ?? null,
            materials: $request->materials,
            printerId: 3,
            coatingId: null,
            unit: 'mm',
            name: $model->name,
            modelName: $request->model_name ?? $model->model_name,
            fileName: $model->file_name,
            thumbName: $thumbName ?? null,
            uploadedThumb: $uploadedThumb,
            modelVolumeCc: $model->model_volume_cc,
            modelXLength: $model->model_x_length,
            modelYLength: $model->model_y_length,
            modelZLength: $model->model_z_length,
            modelBoxVolume: $model->model_box_volume,
            surfaceArea: $model->model_surface_area_cm2,
            modelParts: $model->model_parts ?? 1,
            modelScale: $model->scale ?? 1,
            categories: $categories,
            metaData: $model->meta_data,
        );
    }
}
