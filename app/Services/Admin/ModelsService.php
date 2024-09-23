<?php

namespace App\Services\Admin;

use App\Models\Material;
use App\Models\Model;
use Illuminate\Support\Facades\Storage;

class ModelsService
{
    /**
     * Store a customer completely from API request
     * @param $request
     * @return Model
     */
    public function storeModelFromApi($request): Model
    {
        $material = Material::where('wp_id', $request->wp_id)->first();

        $model = Model::create([
            'material_id' => $material->id,
            'name' => $request->original_file_name,
            'file_name' => 'wp-content/uploads/p3d/' . $request->file_name,
            'model_volume_cc' => $request->material_volume,
            'model_x_length' => $request->x_dim,
            'model_y_length' => $request->y_dim,
            'model_z_length' => $request->z_dim,
            'model_surface_area_cm2' => $request->surface_area,
            'model_parts' => $request->model_parts ?? 1,
            'model_box_volume' => $request->box_volume,
        ]);

        return $model;
    }
}
