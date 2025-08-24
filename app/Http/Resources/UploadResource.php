<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class UploadResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $thumb = sprintf('%s.thumb.png', str_replace('_resized', '', $this->file_name));
        $metaData = $this->meta_data;

        if ($metaData) {
            for ($i = 0, $iMax = count($metaData); $i < $iMax; $i++) {
                if ($metaData[$i]['key'] === 'pa_p3d_scale' && array_key_exists('vakue', $metaData[$i])) {
                    [$value, $n] = explode(' (', str_replace(['&times;', ')'], ['x', ''], $metaData[$i]['value']));
                    $metaData[$i]['value'] = $value;
                }
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
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
            'material_name' => $this->material_name,
            'quantity' => $this->quantity,
            'subtotal' => $this->subtotal,
            'subtotal_tax' => $this->subtotal_tax,
            'total' => $this->total,
            'total_tax' => $this->total_tax,
            'total_refund' => $this->total_refund,
            'total_refund_tax' => $this->total_refund_tax,
            'customer_lead_time' => $this->customer_lead_time,
            'meta_data' => $metaData,
        ];
    }
}
