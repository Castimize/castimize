<?php

namespace App\Http\Resources;

use App\Services\Admin\CalculatePricesService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use stdClass;

class ModelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $thumb = $this->thumb_name ?? sprintf('%s.thumb.png', str_replace('_resized', '', $this->file_name));
        $metaData = $this->meta_data;
//        if ($metaData) {
//            for ($i = 0, $iMax = count($metaData); $i < $iMax; $i++) {
//                if ($metaData[$i]['key'] === 'pa_p3d_scale') {
//                    [$value, $n] = explode(' (', str_replace('&times;', '', $metaData[$i]['value']));
//                    $metaData[$i]['value'] = $value;
//                }
//            }
//        }
        $categoriesRaw = [];
        if ($this->categories !== null) {
            foreach ($this->categories as $category) {
                $categoriesRaw[] = $category['category'];
            }
        }

        $calculatedTotal = null;
        $material = $this->materials->where('wp_id', $request->wp_id)->first();
        $price = $material?->prices->first();
        if ($price) {
            $calculatedTotal = (new CalculatePricesService())->calculatePriceOfModel($price, $this->model_volume_cc, $this->model_surface_area_cm2);
        }

        $fileThumbnail = null;
        $siteThumb = sprintf('%s/%s', env('APP_SITE_URL'), $thumb);
        if (Storage::disk(env('FILESYSTEM_DISK'))->exists($thumb)) {
            $fileThumbnail = sprintf('%s/%s', env('CLOUDFLARE_R2_URL'), $thumb);
        } else {
//            $fileHeaders = get_headers($siteThumb);
//            if (!str_contains($fileHeaders[0], '404')) {
                $fileThumbnail = '/' . $thumb;
//            }
        }

        $thumbnailKey = '3'. $request->wp_id . $this->model_scale . 'mm';

        $isShopOwner = 0;
        if ($this->customer && $this->customer->shopOwner) {
            $isShopOwner = 1;
        }

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'is_shop_owner' => $isShopOwner,
            'shop_listing_id' => $this->shopListingModel?->shop_listing_id ?? null,
            'materials' => MaterialResource::collection($this->materials)->toArray($request),
            'model_name' => $this->model_name,
            'name' => $this->name,
            'file_name' => $this->file_name,
            'raw_file_name' => str_replace('wp-content/uploads/p3d/', '', $this->file_name),
            'file_url' => sprintf('%s/%s', env('CLOUDFLARE_R2_URL'), str_replace('_resized', '', $this->file_name)),
            'file_url_site' => sprintf('%s/%s', env('APP_SITE_URL'), str_replace('_resized', '', $this->file_name)),
            'file_thumbnail' => $fileThumbnail,
            'thumbnail_key' => $thumbnailKey,
            'model_volume_cc' => $this->model_volume_cc,
            'model_volume_cc_display' => round($this->model_volume_cc, 2) . 'cm3',
            'model_x_length' => $this->model_x_length,
            'model_y_length' => $this->model_y_length,
            'model_z_length' => $this->model_z_length,
            'model_surface_area_cm2' => $this->model_surface_area_cm2,
            'model_surface_area_cm2_display' => round($this->model_surface_area_cm2, 2) . 'cm3',
            'model_parts' => $this->model_parts,
            'model_box_volume' => $this->model_box_volume,
            'model_scale' => $this->model_scale,
            'price' => $calculatedTotal,
            'categories_json' => $this->categories,
            'categories' => implode(',', $categoriesRaw),
            'meta_data' => $metaData,
        ];
    }
}
