<?php

namespace App\Http\Resources;

use App\Models\Price;
use App\Services\Admin\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalculatedPriceResource extends JsonResource
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = Price::class;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $currencyService = app(CurrencyService::class);
        return [
            'total' => $currencyService->convertCurrency(config('app.currency'), $request->currency, $this->calculated_total),
        ];
    }
}
