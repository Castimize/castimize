<?php

namespace App\Http\Resources;

use App\Models\Material;
use App\Services\Admin\CalculatePricesService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CalculatedPriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $material = Material::with(['prices'])->where('wp_id', $request->wp_id)->first();
        if ($material === null || $material->prices->count() === 0) {
            throw new NotFoundHttpException(__('Material not found or has no price'));
        }
        $price = $material->prices->first();
        $total = CalculatePricesService::calculatePriceOfModel($price, $request->material_volume, $request->surface_area);
        return [
            'total' => $total,
        ];
    }
}
