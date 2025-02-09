<?php

namespace App\Observers;

use App\Models\ShopOwnerAuth;
use Illuminate\Support\Facades\Crypt;

class ShopOwnerAuthObserver
{
    /**
     * Handle the ShopOwnerAuth "creating" event.
     */
    public function creating(ShopOwnerAuth $shopOwnerAuth): void
    {
//        if ($shopOwnerAuth->shop === 'etsy') {
//            $shopOwnerAuth->shop_oauth = [
//                'client_id' => $shopOwnerAuth->oathKey,
//                'client_secret' => Crypt::encryptString($shopOwnerAuth->oathSecret),
//            ];
//        }
//        $shopOwnerAuth->('oathKey');
//        $shopOwnerAuth->unset('oathSecret');
//        dd($shopOwnerAuth);
    }
}
