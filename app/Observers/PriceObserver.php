<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\Price;

class PriceObserver
{
    /**
     * Handle the Price "creating" event.
     */
    public function creating(Price $price): void
    {
        if ($price->currency_code && $price->currency === null) {
            $currency = Currency::where('code', $price->currency_code)->first();
            if ($currency) {
                $price->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the Price "updating" event.
     */
    public function updating(Price $price): void
    {
        if ($price->currency_code && $price->currency === null) {
            $currency = Currency::where('code', $price->currency_code)->first();
            if ($currency) {
                $price->currency_id = $currency->id;
            }
        }
    }
}
