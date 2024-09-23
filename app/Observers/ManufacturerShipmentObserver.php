<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\ManufacturerShipment;

class ManufacturerShipmentObserver
{
    /**
     * Handle the ManufacturerShipment "creating" event.
     */
    public function creating(ManufacturerShipment $manufacturerShipment): void
    {
        if ($manufacturerShipment->currency_code && $manufacturerShipment->currency === null) {
            $currency = Currency::where('code', $manufacturerShipment->currency_code)->first();
            if ($currency) {
                $manufacturerShipment->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the ManufacturerShipment "updating" event.
     */
    public function updating(ManufacturerShipment $manufacturerShipment): void
    {
        if ($manufacturerShipment->currency_code && $manufacturerShipment->currency === null) {
            $currency = Currency::where('code', $manufacturerShipment->currency_code)->first();
            if ($currency) {
                $manufacturerShipment->currency_id = $currency->id;
            }
        }
    }
}
