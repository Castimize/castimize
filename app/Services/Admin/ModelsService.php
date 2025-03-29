<?php

namespace App\Services\Admin;

use App\DTO\Model\ModelDTO;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ModelsService
{
    public function storeModelFromApi($request, ?Customer $customer = null): Model|null
    {
        $material = Material::where('wp_id', $request->wp_id)->first();
        $fileName = $request->file_name;
        $categories = null;
        if ($request->has('categories')) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }

        if ($customer) {
            $model = $customer->models->where('name', $request->original_file_name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $fileName)
                ->where('material_id', $material->id)
                ->where('model_volume_cc', $request->material_volume)
                ->first();

            if ($model && $model->model_name === $request->model_name) {
                return $model;
            }
        } else {
            $model = Model::where('name', $request->original_file_name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $fileName)
                ->where('material_id', $material->id)
                ->where('model_volume_cc', $request->material_volume)
                ->first();
        }

        $fileNameThumb = sprintf('%s%s.thumb.png', env('APP_SITE_STL_UPLOAD_DIR'), str_replace('_resized', '', $fileName));
        $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $fileName);
        $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
        $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
        $fileHeaders = get_headers($fileUrl);
        $withoutResizedFileName = str_replace('_resized', '', $fileName);

        try {
            // Check files exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileName)) {
                Storage::disk('r2')->put($fileName, file_get_contents($fileUrl));
            }
            // Check files exists on local storage of site and not on R2 (without resized
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($withoutResizedFileName)) {
                Storage::disk('r2')->put($withoutResizedFileName, file_get_contents($fileUrl));
            }
            // Check file thumb exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileNameThumb)) {
                Storage::disk('r2')->put($fileNameThumb, file_get_contents($fileThumb));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        if ($model) {
            if (empty($model->thumb_name)) {
                $model->thumb_name = $fileNameThumb;
                $model->save();
            }
            return $model;
        }

        return Model::create([
            'customer_id' => $customer?->id,
            'material_id' => $material->id,
            'model_name' => $request->model_name ?? null,
            'name' => $request->original_file_name,
            'file_name' => $fileName,
            'thumb_name' => $fileNameThumb,
            'model_volume_cc' => $request->material_volume,
            'model_x_length' => $request->x_dim,
            'model_y_length' => $request->y_dim,
            'model_z_length' => $request->z_dim,
            'model_surface_area_cm2' => $request->surface_area,
            'model_parts' => $request->model_parts ?? 1,
            'model_box_volume' => $request->box_volume,
            'model_scale' => $request->scale ?? 1,
            'meta_data' => $request->meta_data ?? null,
            'categories' => $categories,
        ]);
    }

    public function storeModelFromModelDTO(ModelDTO $modelDTO, ?Customer $customer = null): Model|null
    {
        $material = Material::where('wp_id', $modelDTO->wpId)->first();

        if ($customer) {
            $model = $customer->models->where('name', $modelDTO->name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $modelDTO->fileName)
                ->where('material_id', $material->id)
                ->where('model_volume_cc', $modelDTO->modelVolumeCc)
                ->first();

            if ($model && $model->model_name === $modelDTO->modelName) {
                return $model;
            }
        } else {
            $model = Model::where('name', $modelDTO->name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $modelDTO->fileName)
                ->where('material_id', $material->id)
                ->where('model_volume_cc', $modelDTO->modelVolumeCc)
                ->first();
        }

        $fileNameThumb = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $modelDTO->thumbName);
        $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $modelDTO->fileName);
        $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
        $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
        $fileHeaders = get_headers($fileUrl);
        $withoutResizedFileName = str_replace('_resized', '', $fileName);

        try {
            // Check files exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileName)) {
                Storage::disk('r2')->put($fileName, file_get_contents($fileUrl));
            }
            // Check files exists on local storage of site and not on R2 (without resized
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($withoutResizedFileName)) {
                Storage::disk('r2')->put($withoutResizedFileName, file_get_contents($fileUrl));
            }
            // Check file thumb exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileNameThumb)) {
                Storage::disk('r2')->put($fileNameThumb, file_get_contents($fileThumb));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        if ($model) {
            if (empty($model->thumb_name)) {
                $model->thumb_name = $fileNameThumb;
                $model->save();
            }
            return $model;
        }

        return Model::create([
            'customer_id' => $customer?->id,
            'material_id' => $material->id,
            'model_name' => $modelDTO->modelName,
            'name' => $modelDTO->name,
            'file_name' => $fileName,
            'thumb_name' => $fileNameThumb,
            'model_volume_cc' => $modelDTO->modelVolumeCc,
            'model_x_length' => $modelDTO->modelXLength,
            'model_y_length' => $modelDTO->modelYLength,
            'model_z_length' => $modelDTO->modelZLength,
            'model_surface_area_cm2' => $modelDTO->surfaceArea,
            'model_parts' => $modelDTO->modelParts,
            'model_box_volume' => $modelDTO->modelBoxVolume,
            'model_scale' => $modelDTO->modelScale,
            'meta_data' => $modelDTO->metaData,
            'categories' => $modelDTO->categories,
        ]);
    }

    public function updateModelFromApi($request, Model $model, ?int $customerId = null): Model
    {
        $model->model_name = $request->model_name;
        $categories = null;
        if ($request->has('categories')) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }
        $model->categories = $categories;

        $model->save();

        return $model;
    }
}
