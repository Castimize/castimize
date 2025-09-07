<?php

namespace App\Http\Resources;

use App\Models\Price;
use App\Services\Admin\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalculatedPriceResource extends JsonResource
{
    public $collects = Price::class;

    public function toArray(Request $request): array
    {
        $currencyService = app(CurrencyService::class);

        return [
            'currency' => $request->currency,
            'printer_id' => $request->printer_id ?? null,
            'wp_id' => $request->wp_id,
            'coating_id' => $request->coating_id ?? null,
            'material_volume' => $request->material_volume,
            'support_volume' => $request->support_volume,
            'print_time' => $request->print_time ?? null,
            'box_volume' => $request->box_volume,
            'surface_area' => $request->surface_area,
            'scale' => $request->scale,
            'weight' => $request->weight,
            'x_dim' => $request->x_dim,
            'y_dim' => $request->y_dim,
            'z_dim' => $request->z_dim,
            'polygons' => $request->polygons ?? null,
            'quantity' => $request->quantity,
            'original_file_name' => $request->original_file_name,
            'file_name' => $request->file_name,
            'thumb' => $request->thumb ?? null,
            'total' => $this->calculated_total,
            //            'total' => $currencyService->convertCurrency(config('app.currency'), $request->currency, $this->calculated_total),
        ];
    }
}
