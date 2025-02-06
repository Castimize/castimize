<?php

namespace App\Observers;

use App\Models\ShopOwnerAuth;
use App\Services\Etsy\EtsyService;
use JsonException;

class ShopOwnerAuthObserver
{
    /**
     * Handle the ShopOwnerAuth "creating" event.
     * @throws JsonException
     */
    public function creating(ShopOwnerAuth $shopOwnerAuth): void
    {
        $etsyService = new EtsyService($shopOwnerAuth->shopOwner);
        $etsyService->auth($shopOwnerAuth->oathKey, $shopOwnerAuth->oathSecret);
    }
}
