<?php

namespace App\Http\Resources;

use App\Enums\Admin\CurrencyEnum;
use App\Enums\Shops\ShopOwnerShopsEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'shop' => ShopOwnerShopsEnum::from($this->shop),
            'shop_id' => $this->shop_oauth['shop_id'] ?? null,
            'shop_currency' => array_key_exists('shop_currency', $this->shop_oauth) ? CurrencyEnum::from($this->shop_oauth['shop_currency'])->value : CurrencyEnum::USD->value,
            'taxonomy_id' => $this->shop_oauth['default_taxonomy_id'] ?? null,
            'return_policy_id' => $this->shop_oauth['shop_return_policy_id'] ?? null,
            'shopping_profile_id' => $this->shop_oauth['shop_shipping_profile_id'] ?? null,
        ];
    }
}
