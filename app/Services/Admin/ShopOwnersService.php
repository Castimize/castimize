<?php

namespace App\Services\Admin;

use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Customer;
use App\Models\ShopOwner;
use Illuminate\Http\Request;

class ShopOwnersService
{
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
            'active' => 0,
        ]);
    }
}
