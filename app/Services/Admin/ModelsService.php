<?php

namespace App\Services\Admin;

use App\Models\Material;
use App\Models\Model;
use Illuminate\Support\Facades\Storage;

class ModelsService
{
    public function storeModelFromApi($request, ?int $customerId = null): Model|null
    {
        $material = Material::where('wp_id', $request->wp_id)->first();
        $fileName = env('APP_SITE_STL_UPLOAD_DIR') . $request->file_name;
        $categories = null;
        if ($request->has('categories')) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }

        return Model::create([
            'customer_id' => $customerId,
            'material_id' => $material->id,
            'model_name' => $request->model_name ?? null,
            'name' => $request->original_file_name,
            'file_name' => $fileName,
            'model_volume_cc' => $request->material_volume,
            'model_x_length' => $request->x_dim,
            'model_y_length' => $request->y_dim,
            'model_z_length' => $request->z_dim,
            'model_surface_area_cm2' => $request->surface_area,
            'model_parts' => $request->model_parts ?? 1,
            'model_box_volume' => $request->box_volume,
            'categories' => $categories,
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
