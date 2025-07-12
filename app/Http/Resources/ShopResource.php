<?php

namespace App\Http\Resources;

use AllowDynamicProperties;
use App\Enums\Admin\CurrencyEnum;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Shop;
use App\Services\Etsy\EtsyService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

#[AllowDynamicProperties] class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $url = null;
        if (
            is_array($this->shop_oauth) &&
            ! array_key_exists('access_token', $this->shop_oauth) &&
            $this->shopOwner->customer->vat_number !== null
        ) {
            $shop = Shop::find($this->id);
            $shopOauth = [
                'client_id' => config('services.shops.etsy.client_id'),
                'client_secret' => Crypt::encryptString(config('services.shops.etsy.client_secret')),
            ];
            $shop->shop_oauth = $shopOauth;

            $url = (new EtsyService())->getAuthorizationUrl($shop);
        }

        return [
            'id' => $this->id,
            'shop' => ShopOwnerShopsEnum::from($this->shop)->name,
            'active' => $this->active,
            'shop_id' => array_key_exists('shop_id', $this->shop_oauth) ? $this->shop_oauth['shop_id'] : null,
            'shop_currency' => array_key_exists('shop_currency', $this->shop_oauth) ? CurrencyEnum::from($this->shop_oauth['shop_currency'])->value : CurrencyEnum::USD->value,
            'auth_url' => $url,
        ];
    }
}
