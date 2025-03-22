<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\Shops\Etsy\ListingImageDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Model;
use App\Services\Etsy\EtsyService;
use Etsy\Etsy;
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

        $data = [];
        foreach ($taxonomy->data as $item) {
            $data[] = $item->toArray();
        }

        return response()->json($data);
    }

    public function getShop(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shop = $this->etsyService->getShop($shopOwnerAuth);

        return response()->json($shop->toArray());
    }

    public function getShopReturnPolicy(int $customerId, int $returnPolicyId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shopReturnPolicy = $this->etsyService->getShopReturnPolicy($shopOwnerAuth, $returnPolicyId);

        return response()->json($shopReturnPolicy->toArray());
    }

    public function getShopReturnPolicies(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shopReturnPolicies = $this->etsyService->getShopReturnPolicies($shopOwnerAuth);

        return response()->json($shopReturnPolicies->paginate(100));
    }

    public function createShopReturnPolicy(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shopReturnPolicy = $this->etsyService->createShopReturnPolicy($shopOwnerAuth);

        return response()->json($shopReturnPolicy->toArray());
    }

    public function getListings(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $listings = $this->etsyService->getListings($shopOwnerAuth);

        return response()->json($listings->toJson());
    }

    public function syncListings(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
//        $this->etsyService->refreshAccessToken($shopOwnerAuth);
//        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);
        $models = Model::whereDoesntHave('shopListingModel')->where('customer_id', $customerId)->get();

//        $listingImageDTO = ListingImageDTO::fromModel($shopOwnerAuth->shop_oauth['shop_id'], $models->first());
//        if ($listingImageDTO->image !== '') {
//            $listingImageDTO->listingId = 1882130223;
//            try {
//                $listingImage = $this->etsyService->uploadListingImage($shopOwnerAuth, $listingImageDTO);
//            } catch (\Exception $exception) {
//                dd($exception->getMessage());
//            }
//            dd($listingImage);
//        }


        $listings = $this->etsyService->syncListings($shopOwnerAuth, $models);

        return response()->json(['no']);
//        return response()->json($listings->toArray());
    }

    public function deleteListing(int $customerId, int $listingId)
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $deleted = $this->etsyService->syncListings($shopOwnerAuth, $listingId);

        return response()->json(['deleted' => $deleted]);
    }

    public function getShippingCarriers(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shippingCarriers = $this->etsyService->getShippingCarriers($shopOwnerAuth);


        return response()->json($shippingCarriers->toJson());
    }

    public function getShippingProfile(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shippingProfiles = $this->etsyService->getShippingProfiles($shopOwnerAuth);


        return response()->json($shippingProfiles->toJson());
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

    public function getShopPaymentLedgerEntries(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shopOwnerAuth = $customer->shopOwner->shopOwnerAuths->first();
        $shopPaymentLedgerEntries = $this->etsyService->getShopPaymentAccountLedgerEntries($shopOwnerAuth);


        return response()->json($shopPaymentLedgerEntries->toJson());
    }
}
