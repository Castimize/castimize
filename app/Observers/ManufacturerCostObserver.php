<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\ManufacturerCost;

class ManufacturerCostObserver
{
    /**
     * Handle the ManufacturerCost "creating" event.
     */
    public function creating(ManufacturerCost $manufacturerCost): void
    {
        if ($manufacturerCost->currency_code && $manufacturerCost->currency === null) {
            $currency = Currency::where('code', $manufacturerCost->currency_code)->first();
            if ($currency) {
                $manufacturerCost->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the ManufacturerCost "updating" event.
     */
    public function updating(ManufacturerCost $manufacturerCost): void
    {
        if ($manufacturerCost->currency_code && $manufacturerCost->currency === null) {
            $currency = Currency::where('code', $manufacturerCost->currency_code)->first();
            if ($currency) {
                $manufacturerCost->currency_id = $currency->id;
            }
        }
    }
}
