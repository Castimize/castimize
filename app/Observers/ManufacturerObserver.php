<?php

namespace App\Observers;

use App\Models\City;
use App\Models\Country;
use App\Models\Manufacturer;
use App\Models\State;
use Illuminate\Support\Str;

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
        $state = $manufacturer->state;
        if ($manufacturer->isDirty('stateName')) {
            $state = State::firstOrCreate([
                'name' => $manufacturer->stateName,
            ], [
                'name' => $manufacturer->stateName,
                'slug' => Str::slug($manufacturer->stateName),
                'country_id' => $manufacturer->country_id,
            ]);
            $manufacturer->state_id = $state->id;
        }
        if ($manufacturer->isDirty('cityName')) {
            $city = City::firstOrCreate([
                'name' => $manufacturer->cityName,
            ], [
                'name' => $manufacturer->cityName,
                'slug' => Str::slug($manufacturer->cityName),
                'state_id' => $state?->id,
                'country_id' => $manufacturer->country_id,
            ]);
            $manufacturer->city_id = $city?->id;
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
