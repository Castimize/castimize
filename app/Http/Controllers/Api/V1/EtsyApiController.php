<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Services\Etsy\EtsyService;

class EtsyApiController extends ApiController
{
    public function __construct(
        private EtsyService $etsyService
    ) {
    }

    public function getShop()
    {
        $customer = Customer::find(8);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $this->etsyService->getShop($shopOwnerAuth);
    }

    public function getListing()
    {
        $customer = Customer::find(8);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $this->etsyService->getListing($shopOwnerAuth);
    }
}
