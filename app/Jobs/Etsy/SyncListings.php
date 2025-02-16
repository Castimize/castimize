<?php

declare(strict_types=1);

namespace App\Jobs\Etsy;

use App\Models\Customer;
use App\Models\ShopOwnerAuth;
use App\Services\Etsy\EtsyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncListings implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private ShopOwnerAuth $shopOwnerAuth,
    ) {
    }

    public function handle(): void
    {
        $etsyService = (new EtsyService());
        /** @var Customer $customer */
        $customer = $this->shopOwnerAuth->shopOwner->customer;

        foreach ($customer->models as $model) {
            $etsyService->createListing($this->shopOwnerAuth, $model);
        }
    }
}
