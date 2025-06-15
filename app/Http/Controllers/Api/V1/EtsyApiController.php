<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\Order\OrderDTO;
use App\DTO\Shops\Etsy\ListingImageDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Model;
use App\Models\Shop;
use App\Services\Admin\LogRequestService;
use App\Services\Etsy\EtsyService;
use App\Services\Woocommerce\WoocommerceApiService;
use Etsy\Etsy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EtsyApiController extends ApiController
{
    public function __construct(
        private EtsyService $etsyService,
    ) {
    }

    public function getTaxonomy(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $taxonomy = $this->etsyService->getTaxonomyAsSelect($shop);

//        $data = [];
//        foreach ($taxonomy->data as $item) {
//            $data[] = $item->toArray();
//        }

        return response()->json($taxonomy);
    }

    public function getShop(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $shop = $this->etsyService->getShop($shop);

        return response()->json($shop->toArray());
    }

    public function getShopAuthorizationUrl(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner?->shops?->where('shop', ShopOwnerShopsEnum::Etsy->value)->first();
        if ($shop === null) {
            LogRequestService::addResponse($request, ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $url = $this->etsyService->getAuthorizationUrl($shop);

        return response()->json(['url' => $url]);
    }

    public function getShopReturnPolicy(int $customerId, int $returnPolicyId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $shopReturnPolicy = $this->etsyService->getShopReturnPolicy($shop, $returnPolicyId);

        return response()->json($shopReturnPolicy->toArray());
    }

    public function getShopReturnPolicies(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $shopReturnPolicies = $this->etsyService->getShopReturnPolicies($shop);

        return response()->json($shopReturnPolicies->paginate(100));
    }

    public function createShopReturnPolicy(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $shopReturnPolicy = $this->etsyService->createShopReturnPolicy($shop);

        return response()->json($shopReturnPolicy->toArray());
    }

    public function getListings(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $listings = $this->etsyService->getListings($shop);

        return response()->json($listings->toJson());
    }

    public function getListingProperties(int $customerId, int $listingId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $properties = $this->etsyService->getTaxonomyProperties($shop);

        return response()->json($properties);
    }

    public function getListingInventory(int $customerId, int $listingId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $inventory = $this->etsyService->getListingInventory($shop, $listingId);

        return response()->json($inventory);
    }

    public function syncListings(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
//        $this->etsyService->refreshAccessToken($shop);
//        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);
        $models = Model::whereDoesntHave('shopListingModel')->where('customer_id', $customerId)->get();

//        $listingImageDTO = ListingImageDTO::fromModel($shop->shop_oauth['shop_id'], $models->first());
//        if ($listingImageDTO->image !== '') {
//            $listingImageDTO->listingId = 1882130223;
//            try {
//                $listingImage = $this->etsyService->uploadListingImage($shop, $listingImageDTO);
//            } catch (\Exception $exception) {
//                dd($exception->getMessage());
//            }
//            dd($listingImage);
//        }


        $listings = $this->etsyService->syncListings($shop, $models);

        return response()->json(['no']);
//        return response()->json($listings->toArray());
    }

    public function deleteListing(int $customerId, int $listingId)
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $deleted = $this->etsyService->syncListings($shop, $listingId);

        return response()->json(['deleted' => $deleted]);
    }

    public function getShippingCarriers(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $shippingCarriers = $this->etsyService->getShippingCarriers($shop);


        return response()->json($shippingCarriers->toJson());
    }

    public function getShippingProfile(int $customerId): JsonResponse
    {
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $shippingProfiles = $this->etsyService->getShippingProfiles($shop);


        return response()->json($shippingProfiles->toJson());
    }

    public function createShippingProfile(int $customerId): JsonResponse
    {
        $countries = Country::with(['logisticsZone.shippingFee'])->get();
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->first();
        $shopId = $shop->shop_oauth['shop_id'];

        $shippingProfileDTO = $this->etsyService->createShippingProfile(
            shop: $shop,
            shippingProfileDTO: ShippingProfileDTO::fromShop($shopId),
        );

        foreach ($countries as $country) {
            if ($country->has('logisticsZone')) {
                $shippingProfileDestinationDTO = $this->etsyService->createShippingProfileDestination(
                    shop: $shop,
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
        $shop = $customer->shopOwner->shops->first();
        $shopPaymentLedgerEntries = $this->etsyService->getShopPaymentAccountLedgerEntries($shop);


        return response()->json($shopPaymentLedgerEntries->toJson());
    }

    public function getShopReceipts(int $customerId): JsonResponse
    {
//        $etsyService = app(EtsyService::class);
//        $woocommerceApiService = app(WoocommerceApiService::class);
//
//        $shops = Shop::with(['shopOwner.customer'])->where('active', true)->where('shop', ShopOwnerShopsEnum::Etsy->value)->get();
//
//        foreach ($shops as $shop) {
//            $receipts = $etsyService->getShopReceipts($shop);
//            foreach ($receipts->data as $receipt) {
//                dd($receipt);
//                $lines = $etsyService->getShopListingsFromReceipt($shop, $receipt);
//                if (count($lines) > 0) {
//                    $orderDTO = OrderDTO::fromEtsyReceipt($shop, $receipt, $lines);
//                    dd($orderDTO);
//                    $woocommerceApiService->createOrder($orderDTO);
//                }
//            }
//        }
        $customer = Customer::find($customerId);
        $shop = $customer->shopOwner->shops->where('shop', ShopOwnerShopsEnum::Etsy->value)->first();
        $shopReceipts = $this->etsyService->getShopReceipts($shop, ['min_created' => now()->subDays(2)->timestamp]);

        $response = [];

        foreach ($shopReceipts->data as $shopReceipt) {
            $response[] = $shopReceipt->toArray();
        }

        return response()->json($response);
    }
}
