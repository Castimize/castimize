<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $lastOrderArray = [];
        $lastOrder = $this->orders->first();
        if ($lastOrder) {
            $lastOrderArray['id'] = $lastOrder->id;
            $lastOrderArray['wp_id'] = $lastOrder->wp_id;
            $lastOrderArray['created_at'] = $lastOrder->created_at;
        }

        $billingAddress = $this->addresses()->wherePivot('default_billing', 1)->first();
        $shippingAddress = $this->addresses()->wherePivot('default_shipping', 1)->first();

        return [
            'wp_id' => $this->wp_id,
            'email' => $this->email ?? $this->user?->email,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'username' => $this->user?->username,
            'last_order' => $lastOrderArray,
            'order_count' => $this->orders->count(),
            'avatar' => $this->user?->avatar ? env('CLOUDFLARE_R2_URL') . $this->user->avatar : null,
            'date_created' => $this->created_at,
            'date_modified' => $this->updated_at,
            'billing' => $billingAddress ? (new AddressResource($billingAddress))->toArray($request) : [],
            'shipping' => $shippingAddress ? (new AddressResource($shippingAddress))->toArray($request) : [],
        ];
    }
}
