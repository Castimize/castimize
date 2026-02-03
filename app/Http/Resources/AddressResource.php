<?php

namespace App\Http\Resources;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Address
 */
class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'address_line_1' => $this->address_line1,
            'address_line_2' => $this->address_line2,
            'postal_code' => $this->postal_code,
            'city' => $this->city?->name,
            'state' => $this->state?->name,
            'country' => $this->country?->alpha2,
            'contact_name' => $this->pivot?->contact_name,
            'phone' => $this->pivot?->phone,
            'email' => $this->pivot?->email,
        ];
    }
}
