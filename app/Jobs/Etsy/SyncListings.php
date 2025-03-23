<?php

declare(strict_types=1);

namespace App\Jobs\Etsy;

use App\Models\Customer;
use App\Models\ShopOwnerAuth;
use App\Services\Etsy\EtsyService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncListings implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private ShopOwnerAuth $shopOwnerAuth,
        private int $taxonomyId,
    ) {
    }

    public function handle(): void
    {
        $etsyService = (new EtsyService());
        /** @var Customer $customer */
        $customer = $this->shopOwnerAuth->shopOwner->customer;
        $models = $customer->models()->doesntHave('shopListingModel')->get();

        $etsyService->syncListings($this->shopOwnerAuth, $models);
    }
}
