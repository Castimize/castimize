<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Models\Country;
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

        return response()->json($taxonomy->toJson());
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

    public function getShippingCarriers(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shippingCarriers = $this->etsyService->getShippingCarriers($shopOwnerAuth);


        return response()->json($shippingCarriers->toJson());
    }

    public function createShippingProfile(int $customerId): JsonResponse
    {
        $countries = Country::with(['logisticsZone.shippingFee'])->get();
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shopId = $shopOwnerAuth->shop_oauth['shop_id'];

        $shippingProfileDTO = $this->etsyService->createShippingProfile(
            shopOwnerAuth: $shopOwnerAuth,
            shippingProfileDTO: ShippingProfileDTO::fromShop($shopId)
        );

        foreach ($countries as $country) {
            if ($country->has('logisticsZone')) {
                $shippingProfileDestinationDTO = $this->etsyService->createShippingProfileDestination(
                    shopOwnerAuth: $shopOwnerAuth,
                    shippingProfileDestinationDTO: ShippingProfileDestinationDTO::fromCountry(
                        shopId: $shopId,
                        country: $country,
                        shippingProfileId: $shippingProfileDTO->shippingProfileId),
                );

                $shippingProfileDTO->shippingProfileDestinations->push($shippingProfileDestinationDTO);
            }
        }

        return response()->json($shippingProfileDTO);
    }
}
