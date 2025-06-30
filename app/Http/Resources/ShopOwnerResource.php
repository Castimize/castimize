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
        if ($this->resource === null) {
            return [
                'id' => null,
                'is_shop_owner' => false,
                'active' => false,
                'customer_id' => null,
                'vat_number' => null,
                'stripe_id' => null,
                'stripe_mandate_id' => null,
                'shops' => [],
                'shops_list' => ShopOwnerShopsEnum::cases(),
            ];
        }

        return [
            'id' => $this->id,
            'is_shop_owner' => true,
            'active' => $this->active,
            'vat_number' => $this->customer->vat_number,
            'stripe_id' => is_array($this->customer->stripe_data) && array_key_exists('stripe_id', $this->customer->stripe_data) ? $this->customer->stripe_data['stripe_id'] : null,
            'stripe_mandate_id' => is_array($this->customer->stripe_data) && array_key_exists('mandate_id', $this->customer->stripe_data) ? $this->customer->stripe_data['mandate_id'] : null,
            'shops' => ShopResource::collection($this->shops)->toArray($request),
            'shops_list' => ShopOwnerShopsEnum::cases(),
        ];
    }
}
