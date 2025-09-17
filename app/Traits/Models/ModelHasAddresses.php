<?php

namespace App\Traits\Models;

use App\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait ModelHasAddresses
{
    public function addresses(): MorphToMany
    {
        return $this->morphToMany(
            Address::class,
            'model',
            'model_has_addresses'
        )->withPivot([
            'default_billing',
            'default_shipping',
            'company',
            'contact_name',
            'phone',
            'email',
        ]);
    }
}
