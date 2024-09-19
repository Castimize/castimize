<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\Material;

class MaterialObserver
{
    /**
     * Handle the Material "creating" event.
     */
    public function creating(Material $material): void
    {
        if ($material->currency_code && $material->currency === null) {
            $currency = Currency::where('code', $material->currency_code)->first();
            if ($currency) {
                $material->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the Material "updating" event.
     */
    public function updating(Material $material): void
    {
        if ($material->currency_code && $material->currency === null) {
            $currency = Currency::where('code', $material->currency_code)->first();
            if ($currency) {
                $material->currency_id = $currency->id;
            }
        }
    }
}
