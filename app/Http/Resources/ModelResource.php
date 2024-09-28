<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use function Aws\map;

class ModelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $thumb = sprintf('%s.thumb.png', $this->file_name);
        $metaData = $this->meta_data;
        if ($metaData) {
            for ($i = 0, $iMax = count($metaData); $i < $iMax; $i++) {
                if ($metaData[$i]['key'] === 'pa_p3d_scale') {
                    [$value, $n] = explode(' (', str_replace('&times;', '', $metaData[$i]['value']));
                    $metaData[$i]['value'] = $value;
                }
            }
        }
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'material_name' => $this->material->name,
            'material_id' => $this->material->id,
            'material_wp_id' => $this->material->wp_id,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'raw_file_name' => str_replace('wp-content/uploads/p3d/', '', $this->file_name),
            'file_url' => sprintf('%s/%s', env('CLOUDFLARE_R2_URL'), $this->file_name),
            'file_thumbnail' => Storage::disk(env('FILESYSTEM_DISK'))->exists($thumb) ? sprintf('%s/%s', env('CLOUDFLARE_R2_URL'), $thumb) : '',
            'model_volume_cc' => $this->model_volume_cc,
            'model_x_length' => $this->model_x_length,
            'model_y_length' => $this->model_y_length,
            'model_z_length' => $this->model_z_length,
            'model_surface_area_cm2' => $this->model_surface_area_cm2,
            'model_parts' => $this->model_parts,
            'model_box_volume' => $this->model_box_volume,
            'meta_data' => $metaData,
        ];
    }
}
