<?php

namespace App\Observers;

use App\Enums\Admin\CurrencyEnum;
use App\Models\Currency;
use App\Models\ShippingFee;

class ShippingFeeObserver
{
    /**
     * Handle the ShippingFee "creating" event.
     */
    public function creating(ShippingFee $shippingFee): void
    {
        if (! $shippingFee->currency_code) {
            $shippingFee->currency_code = CurrencyEnum::USD->value;
        }
        if ($shippingFee->currency_code && $shippingFee->currency === null) {
            $currency = Currency::where('code', $shippingFee->currency_code)->first();
            if ($currency) {
                $shippingFee->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the ShippingFee "updating" event.
     */
    public function updating(ShippingFee $shippingFee): void
    {
        if (! $shippingFee->currency_code) {
            $shippingFee->currency_code = CurrencyEnum::USD->value;
        }
        if ($shippingFee->currency_code && $shippingFee->currency === null) {
            $currency = Currency::where('code', $shippingFee->currency_code)->first();
            if ($currency) {
                $shippingFee->currency_id = $currency->id;
            }
        }
    }
}
