<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Customer;
use App\Models\Model;
use App\Models\Shop;
use App\Services\Admin\LogRequestService;
use App\Services\Etsy\EtsyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EtsyApiController extends ApiController
{
    public function __construct(
        private EtsyService $etsyService,
    ) {}

    public function getTaxonomy(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $taxonomy = $this->etsyService->getTaxonomyAsSelect($shop);

        return response()->json($taxonomy);
    }

    public function getShop(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $etsyShop = $this->etsyService->getShop($shop);

        return response()->json($etsyShop->toArray());
    }

    public function getShopAuthorizationUrl(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::with('shopOwner.shops')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse($request, ['message' => 'Customer not found'], 404);
            abort(Response::HTTP_NOT_FOUND, 'Customer not found');
        }

        $shop = $customer->shopOwner?->shops?->where('shop', ShopOwnerShopsEnum::Etsy->value)->first();
        if ($shop === null) {
            LogRequestService::addResponse($request, ['message' => 'Etsy shop not found'], 404);
            abort(Response::HTTP_NOT_FOUND, 'Etsy shop not found');
        }

        $url = $this->etsyService->getAuthorizationUrl($shop);

        return response()->json([
            'url' => $url,
        ]);
    }

    public function getShopReturnPolicy(int $customerId, int $returnPolicyId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shopReturnPolicy = $this->etsyService->getShopReturnPolicy($shop, $returnPolicyId);

        return response()->json($shopReturnPolicy->toArray());
    }

    public function getShopReturnPolicies(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shopReturnPolicies = $this->etsyService->getShopReturnPolicies($shop);

        return response()->json($shopReturnPolicies->paginate(100));
    }

    public function createShopReturnPolicy(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shopReturnPolicy = $this->etsyService->createShopReturnPolicy($shop);

        return response()->json($shopReturnPolicy->toArray());
    }

    public function getListings(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $listings = $this->etsyService->getListings($shop);

        return response()->json($listings->toJson());
    }

    public function getListingProperties(int $customerId, int $listingId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $properties = $this->etsyService->getTaxonomyProperties($shop);

        return response()->json($properties);
    }

    public function getListingInventory(int $customerId, int $listingId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $inventory = $this->etsyService->getListingInventory($shop, $listingId);

        return response()->json($inventory);
    }

    public function syncListings(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $models = Model::whereDoesntHave('shopListingModel')->where('customer_id', $customerId)->get();
        $listings = $this->etsyService->syncListings($shop, $models);

        return response()->json($listings->toArray());
    }

    public function deleteListing(int $customerId, int $listingId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $deleted = $this->etsyService->deleteListing($shop, $listingId);

        return response()->json([
            'deleted' => $deleted,
        ]);
    }

    public function getShippingCarriers(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shippingCarriers = $this->etsyService->getShippingCarriers($shop);

        return response()->json($shippingCarriers->toJson());
    }

    public function getShippingProfile(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shippingProfiles = $this->etsyService->getShippingProfiles($shop);

        return response()->json($shippingProfiles->toJson());
    }

    public function createShippingProfile(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shopId = $shop->shop_oauth['shop_id'];

        $shippingProfileDTO = $this->etsyService->createShippingProfile(
            shop: $shop,
            shippingProfileDTO: ShippingProfileDTO::fromShop($shopId),
        );

        return response()->json($shippingProfileDTO);
    }

    public function getShopPaymentLedgerEntries(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shopPaymentLedgerEntries = $this->etsyService->getShopPaymentAccountLedgerEntries($shop);

        return response()->json($shopPaymentLedgerEntries->toJson());
    }

    public function getShopReceipts(int $customerId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shopReceipts = $this->etsyService->getShopReceipts($shop, [
            'min_created' => now()->subDays(14)->timestamp,
        ]);

        $response = [];
        foreach ($shopReceipts->data as $shopReceipt) {
            $response[] = $shopReceipt->toArray();
        }

        return response()->json($response);
    }

    public function getShopReceipt(int $customerId, int $receiptId): JsonResponse
    {
        $shop = $this->getEtsyShopOrFail($customerId);
        $shopReceipt = $this->etsyService->getShopReceipt(
            shop: $shop,
            receiptId: $receiptId,
        );

        return response()->json($shopReceipt->toArray());
    }

    /**
     * Get the Etsy shop for a customer or abort with 404.
     */
    private function getEtsyShopOrFail(int $customerId): Shop
    {
        $customer = Customer::with('shopOwner.shops')->find($customerId);

        if ($customer === null) {
            abort(Response::HTTP_NOT_FOUND, "Customer {$customerId} not found");
        }

        if ($customer->shopOwner === null) {
            abort(Response::HTTP_NOT_FOUND, "Customer {$customerId} has no shop owner");
        }

        $shop = $customer->shopOwner->shops
            ->where('shop', ShopOwnerShopsEnum::Etsy->value)
            ->first();

        if ($shop === null) {
            abort(Response::HTTP_NOT_FOUND, "Customer {$customerId} has no Etsy shop");
        }

        return $shop;
    }
}
