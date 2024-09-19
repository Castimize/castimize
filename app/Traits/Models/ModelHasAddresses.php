<?php

namespace App\Traits\Models;

use App\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait ModelHasAddresses
{
    /**
     * @return MorphToMany
     */
    public function addresses(): MorphToMany
    {
        return $this->morphToMany(
            Address::class,
            'model',
            'model_has_addresses'
        )->withPivot([
            'default_billing',
            'default_shipping',
            'contact_name',
            'phone',
            'email',
        ]);
    }
}
