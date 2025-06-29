<?php

namespace App\Http\Resources;

use App\Enums\Shops\ShopOwnerShopsEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopOwnerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'vat_number' => $this->customer->vat_number,
            'stripe_id' => is_array($this->customer->stripe_data) && array_key_exists('stripe_id', $this->customer->stripe_data) ? $this->customer->stripe_data['stripe_id'] : null,
            'stripe_mandate_id' => is_array($this->customer->stripe_data) && array_key_exists('mandate_id', $this->customer->stripe_data) ? $this->customer->stripe_data['mandate_id'] : null,
            'shops' => ShopResource::collection($this->shops)->toArray($request),
            'shops_list' => ShopOwnerShopsEnum::cases(),
        ];
    }
}
