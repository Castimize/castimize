<?php

namespace App\Services\Admin;

use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Customer;
use App\Models\Shop;
use App\Models\ShopOwner;
use Illuminate\Http\Request;

class ShopOwnersService
{
    public function update(ShopOwner $shopOwner, array $data): ShopOwner
    {
        $shopOwner->update($data);
        $shopOwner->refresh();

        return $shopOwner;
    }

    public function createShopOwner(Customer $customer)
    {
        return $customer->shopOwner()->create([
            'created_at' => now(),
            'created_by' => 1,
        ]);
    }

    public function createShop(Request $request, ShopOwner $shopOwner)
    {
        return $shopOwner->shops()->create([
            'shop' => ShopOwnerShopsEnum::tryFrom($request->shop)->value,
            'shop_oauth' => [],
            'active' => 0,
        ]);
    }

    public function setShopsActiveState(ShopOwner $shopOwner, bool $active): void
    {
        foreach ($shopOwner->shops as $shop) {
            $this->setShopActiveState($shop, $active);
        }
    }

    public function setShopActiveState(Shop $shop, bool $active): Shop
    {
        $shop->active = $active;
        $shop->save();
        $shop->refresh();

        return $shop;
    }
}
