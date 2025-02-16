<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Services\Etsy\EtsyService;
use Illuminate\Http\JsonResponse;

class EtsyApiController extends ApiController
{
    public function __construct(
        private EtsyService $etsyService,
    ) {
    }

    public function getTaxonomy(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $taxonomy = $this->etsyService->getSellerTaxonomy($shopOwnerAuth);
        foreach ($taxonomy->data as $item) {
            var_dump($item);
            if ($item->name === 'Prints') {
                dd($item->children);
            }
        }

        return response()->json($taxonomy);
    }

    public function getShop(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shop = $this->etsyService->getShop($shopOwnerAuth);

        return response()->json($shop);
    }

    public function getListings(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $listings = $this->etsyService->getListings($shopOwnerAuth);

        return response()->json($listings);
    }
}
