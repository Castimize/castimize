<?php

namespace App\Observers;

use App\Models\Country;
use App\Models\Manufacturer;

class ManufacturerObserver
{
    /**
     * Handle the Manufacturer "creating" event.
     */
    public function creating(Manufacturer $manufacturer): void
    {
        $country = Country::find($manufacturer->country_id);
        if ($country) {
            $manufacturer->country_code = strtoupper($country->alpha2);
        }

        $manufacturer->validateAddress();
    }

    /**
     * Handle the Manufacturer "created" event.
     */
    public function created(Manufacturer $manufacturer): void
    {
        $manufacturer->user->assignRole('manufacturer');
    }

    /**
     * Handle the Manufacturer "updating" event.
     */
    public function updating(Manufacturer $manufacturer): void
    {
        if ($manufacturer->isDirty('country_id')) {
            $country = Country::find($manufacturer->country_id);
            if ($country) {
                $manufacturer->country_code = strtoupper($country->alpha2);
            }
        }

        $manufacturer->validateAddress();
    }

    /**
     * Handle the Manufacturer "updated" event.
     */
    public function updated(Manufacturer $manufacturer): void
    {
        if (!$manufacturer->user->hasRole('manufacturer')) {
            $manufacturer->user->assignRole('manufacturer');
        }
    }
}
