<?php

declare(strict_types=1);

namespace App\DTO\Model;

use App\Models\Material;
use App\Models\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

class ModelDTO extends Data
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
    ) {}

    public static function fromApiRequest(Request $request, ?int $customerId = null): self
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
        $uploadedThumb = false;
        $thumbName = self::defineThumbImageName($request);

        return new self(
            wpId: (string) $request->wp_id,
            customerId: $customerId,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [$material],
            printerId: (int) ($request->printer_id ?? 3),
            coatingId: isset($request->coating_id) ? (int) $request->coating_id : null,
            unit: 'mm',
            name: $request->original_file_name ?? $request->file_name ?? '',
            modelName: $request->model_name ?? null,
            fileName: $request->file_name ?? '',
            thumbName: $thumbName,
            uploadedThumb: $uploadedThumb,
            modelVolumeCc: (float) ($request->material_volume ?? 0),
            modelXLength: (float) ($request->x_dim ?? 0),
            modelYLength: (float) ($request->y_dim ?? 0),
            modelZLength: (float) ($request->z_dim ?? 0),
            modelBoxVolume: (float) ($request->box_volume ?? 0),
            surfaceArea: (float) ($request->surface_area ?? 0),
            modelParts: (int) ($request->model_parts ?? 1),
            modelScale: (float) ($request->scale ? number_format(round((float) $request->scale, 4), 4) : 1),
            categories: $categories,
            metaData: $request->meta_data ?? null,
        );
    }

    public static function fromWpRequest(Request $request, int $customerId): self
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
            $thumbName = time().'_'.str_replace(' ', '_', $thumbFileNameWithoutExt).'.'.$thumbFileExtension;
            Storage::disk('s3')->putFileAs(config('app.stl_upload_dir'), $file, $thumbName);
        } else {
            $uploadedThumb = false;
            $thumbName = self::defineThumbImageName($request);
        }

        return new self(
            wpId: (string) $request->wp_id,
            customerId: $customerId,
            shopListingId: null,
            shopTaxonomyId: null,
            materials: [$material],
            printerId: (int) ($request->printer_id ?? 3),
            coatingId: isset($request->coating_id) ? (int) $request->coating_id : null,
            unit: 'mm',
            name: $request->original_file_name ?? $request->file_name ?? '',
            modelName: $request->model_name ?? null,
            fileName: $request->file_name ?? '',
            thumbName: $thumbName,
            uploadedThumb: $uploadedThumb,
            modelVolumeCc: (float) ($request->material_volume ?? 0),
            modelXLength: (float) ($request->x_dim ?? 0),
            modelYLength: (float) ($request->y_dim ?? 0),
            modelZLength: (float) ($request->z_dim ?? 0),
            modelBoxVolume: (float) ($request->box_volume ?? 0),
            surfaceArea: (float) ($request->surface_area ?? 0),
            modelParts: (int) ($request->model_parts ?? 1),
            modelScale: (float) ($request->scale ? number_format(round((float) $request->scale, 4), 4) : 1),
            categories: $categories,
            metaData: $request->meta_data ?? null,
        );
    }

    public static function fromWpUpdateRequest(Request $request, Model $model, int $customerId): self
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
            $thumbName = time().'_'.str_replace(' ', '_', $thumbFileNameWithoutExt).'.'.$thumbFileExtension;
            Storage::disk('s3')->putFileAs(config('app.stl_upload_dir'), $file, $thumbName);
        }

        return new self(
            wpId: (string) $model->materials->first()->wp_id,
            customerId: $customerId,
            shopListingId: isset($request->shop_listing_id) ? (int) $request->shop_listing_id : null,
            shopTaxonomyId: isset($request->shop_taxonomy_id) ? (int) $request->shop_taxonomy_id : null,
            materials: $request->materials,
            printerId: 3,
            coatingId: null,
            unit: 'mm',
            name: $model->name,
            modelName: $request->model_name ?? $model->model_name,
            fileName: $model->file_name,
            thumbName: $thumbName ?? null,
            uploadedThumb: $uploadedThumb,
            modelVolumeCc: (float) $model->model_volume_cc,
            modelXLength: (float) $model->model_x_length,
            modelYLength: (float) $model->model_y_length,
            modelZLength: (float) $model->model_z_length,
            modelBoxVolume: (float) $model->model_box_volume,
            surfaceArea: (float) $model->model_surface_area_cm2,
            modelParts: (int) ($model->model_parts ?? 1),
            modelScale: (float) ($model->scale ?? 1),
            categories: $categories,
            metaData: $model->meta_data,
        );
    }

    private static function defineThumbImageName(Request $request): string|array
    {
        $thumbName = sprintf(
            '%s_%s%s%s%s%s.thumb.png',
            str_replace('_resized', '', $request->file_name),
            $request->printer_id ?? 3,
            $request->wp_id ?? 1,
            $request->coating_id ?? null,
            $request->scale ?? 1,
            'mm',
        );

        $fileNameThumb = sprintf('%s%s', config('app.stl_upload_dir'), $thumbName);
        $fileThumb = sprintf('%s/%s', config('app.site_url'), $fileNameThumb);
        $fileHeaders = get_headers($fileThumb);
        if (str_contains($fileHeaders[0], '404')) {
            $thumbName = sprintf(
                '%s_%s%s%s%s%s.thumb.png',
                str_replace('_resized', '', $request->file_name),
                $request->printer_id ?? 3,
                1,
                $request->coating_id ?? null,
                1,
                'mm',
            );

            $fileNameThumb = sprintf('%s%s', config('app.stl_upload_dir'), $thumbName);
            $fileThumb = sprintf('%s/%s', config('app.url'), $fileNameThumb);
            $fileHeaders = get_headers($fileThumb);
            if (str_contains($fileHeaders[0], '404')) {
                $model = Model::where('file_name', 'like', '%'.str_replace('_resized', '', $request->file_name).'%')->first();
                if ($model) {
                    $thumbName = str_replace(config('app.stl_upload_dir'), '', $model->thumb_name);
                }
            }
        }

        return $thumbName;
    }
}
