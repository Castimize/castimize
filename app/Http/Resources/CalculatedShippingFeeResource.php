<?php

namespace App\Http\Resources;

use App\Models\ShippingFee;
use App\Services\Admin\CurrencyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ShippingFee
 */
class CalculatedShippingFeeResource extends JsonResource
{
    public $collects = ShippingFee::class;

    public function toArray(Request $request): array
    {
        $currencyService = app(CurrencyService::class);

        return [
            'logistics_zone_id' => $this->logistics_zone_id,
            'currency_id' => $this->currency_id,
            'name' => $this->name,
            'default_rate' => $this->default_rate,
            'currency_code' => $this->currency_code,
            'default_lead_time' => $this->default_lead_time,
            'cc_threshold_1' => $this->cc_threshold_1,
            'rate_increase_1' => $this->rate_increase_1,
            'cc_threshold_2' => $this->cc_threshold_2,
            'rate_increase_2' => $this->rate_increase_2,
            'cc_threshold_3' => $this->cc_threshold_3,
            'rate_increase_3' => $this->rate_increase_3,
            'calculated_total_raw' => $this->calculated_total,
            'calculated_total' => $currencyService->convertCurrency(config('app.currency'), $request->currency, $this->calculated_total),
        ];
    }
}
