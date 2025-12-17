<?php

declare(strict_types=1);

namespace App\Services\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ListingImageDTO;
use App\DTO\Shops\Etsy\ReceiptTrackingDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Enums\Etsy\EtsyListingStatesEnum;
use App\Models\Model;
use App\Models\Shop;
use App\Models\ShopListingModel;
use App\Services\Admin\ShopListingModelService;
use Carbon\Carbon;
use Etsy\Collection;
use Etsy\Etsy;
use Etsy\OAuth\Client;
use Etsy\Resources\LedgerEntry;
use Etsy\Resources\ListingImage;
use Etsy\Resources\Receipt;
use Etsy\Resources\ReturnPolicy;
use Etsy\Resources\SellerTaxonomy;
use Etsy\Resources\ShippingCarrier;
use Etsy\Resources\ShippingProfile;
use Etsy\Resources\Shop as EtsyShop;
use Etsy\Resources\Transaction;
use Etsy\Resources\User;
use Etsy\Utils\PermissionScopes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class EtsyService
{
    private $client;

    public function getRedirectUri(): string
    {
        return URL::route(
            name: 'providers.etsy.oauth',
        );
    }

    public function getAuthorizationUrl(Shop $shop): string
    {
        $this->client = new Client(client_id: $shop->shop_oauth['client_id']);
        $scopes = PermissionScopes::ALL_SCOPES;
//        $scopes = ['listings_d', 'listings_r', 'listings_w', 'profile_r'];

        [$verifier, $code_challenge] = $this->client->generateChallengeCode();
        $nonce = $this->client->createNonce();

        $shopOauth = $shop->shop_oauth;
        $shopOauth['verifier'] = $verifier;
        $shopOauth['nonce'] = $nonce;

        $shop->shop_oauth = $shopOauth;
        $shop->save();

        return $this->client->getAuthorizationUrl(
            redirect_uri: $this->getRedirectUri(),
            scope: $scopes,
            code_challenge: $code_challenge,
            nonce: $nonce,
        );
    }

    public function requestAccessToken(Request $request): void
    {
        $nonce = $request->state;
        $code = $request->code;
        $shop = Shop::whereJsonContains('shop_oauth->nonce', $nonce)->first();

        if ($shop === null) {
            throw new Exception(__('Shop not found'));
        }

        $this->client = new Client(client_id: $shop->shop_oauth['client_id']);

        $response = $this->client->requestAccessToken(
            redirect_uri: $this->getRedirectUri(),
            code: $code,
            verifier: $shop->shop_oauth['verifier'],
        );

        $shop = $this->storeAccessToken($shop, $response);

        new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        $etsyShop = $this->addShopToShopOwnerShop($shop);

        $this->checkExistingShippingProfile(
            shopId: $etsyShop->shop_id,
            shop: $shop,
        );
        $this->createShopReturnPolicy($shop);
    }

    public function refreshAccessToken(Shop $shop): void
    {
        if (! array_key_exists('refresh_token', $shop->shop_oauth) || ! array_key_exists('access_token', $shop->shop_oauth)) {
            $shop->active = 0;
            $shop->save();
            throw new Exception(__('Shop is unauthenticated :shopOwner', [
                'shopOwner' => $shop->shop_owner_id,
            ]));
        }
        $this->client = new Client(
            client_id: $shop->shop_oauth['client_id'],
        );
        $response = $this->client->refreshAccessToken($shop->shop_oauth['refresh_token']);

        $this->storeAccessToken($shop, $response);
    }

    public function getShop(Shop $shop): EtsyShop|null
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return $this->addShopToShopOwnerShop($shop);
    }

    public function getShopReturnPolicy(Shop $shop, int $returnPolicyId): ReturnPolicy
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return ReturnPolicy::get(
            shop_id: $shop->shop_oauth['shop_id'],
            policy_id: $returnPolicyId,
        );
    }

    public function getShopReturnPolicies(Shop $shop): Collection
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return ReturnPolicy::all(
            shop_id: $shop->shop_oauth['shop_id'],
        );
    }

    public function createShopReturnPolicy(Shop $shop): ReturnPolicy
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        $shopReturnPolicy = ReturnPolicy::create(
            shop_id: $shop->shop_oauth['shop_id'],
            data: [
                'accepts_returns' => false,
                'accepts_exchanges' => false,
                'return_deadline' => null,
            ],
        );

        $this->addReturnPolicyToShopOwnerShop($shop, $shopReturnPolicy);

        return $shopReturnPolicy;
    }

    public function getTaxonomyAsSelect(Shop $shop): array
    {
        $taxonomy = $this->getSellerTaxonomy($shop);

        $data = [];
        foreach ($taxonomy->data as $item) {
            $this->getChildrenCategories($data, $item);
        }

        return $data;
    }

    protected function getChildrenCategories(&$allCategories, $category): void
    {
        $allCategories[$category->id] = [
            'id' => $category->id,
            'level' => $category->level,
            'name' => $category->name,
            'parent_id' => $category->parent_id,
            'full_path' => property_exists($category, 'full_path_taxonomy_ids') ? implode(',', $category->full_path_taxonomy_ids) : '',
        ];

        if ($category->children) {
            foreach ($category->children as $child) {
                $this->getChildrenCategories($allCategories, $child);
            }
        }
    }

    public function getSellerTaxonomy(Shop $shop): Collection
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return SellerTaxonomy::all();
    }

    public function checkExistingShippingProfile(int $shopId, Shop $shop)
    {
        $shippingProfileDTO = ShippingProfileDTO::fromShop($shopId);
        $shippingProfiles = $this->getShippingProfiles($shop);
        $createShippingProfile = true;
        foreach ($shippingProfiles->data as $shippingProfile) {
            if ($shippingProfile->title === $shippingProfileDTO->title || $shippingProfile->shipping_profile_id === $shippingProfileDTO->shippingProfileId) {
                $createShippingProfile = false;

                $shippingProfileDTO->shippingProfileId = $shippingProfile->shipping_profile_id;

                $this->addShippingProfileToShopOwnerShop($shop, $shippingProfile);
                $shop->refresh();
            }
        }

        if ($createShippingProfile) {
            $this->createShippingProfile($shop, $shippingProfileDTO);
        }
    }

    public function getShippingProfile(Shop $shop)
    {
        $this->refreshAccessToken($shop);

        return (new EtsyShippingProfileService(
            shop: $shop,
        ))->getShippingProfile();
    }

    public function getShippingProfiles(Shop $shop)
    {
        $this->refreshAccessToken($shop);

        return (new EtsyShippingProfileService(
            shop: $shop,
        ))->getShippingProfiles();
    }

    public function createShippingProfile(Shop $shop, ShippingProfileDTO $shippingProfileDTO): ShippingProfileDTO
    {
        $this->refreshAccessToken($shop);

        return (new EtsyShippingProfileService(
            shop: $shop,
        ))->createShippingProfile($shippingProfileDTO);
    }

    public function createShippingProfileDestination(Shop $shop, ShippingProfileDestinationDTO $shippingProfileDestinationDTO): ShippingProfileDestinationDTO
    {
        $this->refreshAccessToken($shop);

        return (new EtsyShippingProfileService(
            shop: $shop,
        ))->createShippingProfileDestination($shippingProfileDestinationDTO);
    }

    public function getListing(Shop $shop, int $listingId)
    {
        $this->refreshAccessToken($shop);
        return (new EtsyListingService(
            shop: $shop,
        ))->getListing(
            listingId: $listingId,
        );
    }

    public function getListings(Shop $shop): Collection
    {
        $this->refreshAccessToken($shop);
        return (new EtsyListingService(
            shop: $shop,
        ))->getListings();
    }

    public function getListingProperty(Shop $shop, int $listingId)
    {
        $this->refreshAccessToken($shop);
        $properties = $this->client->get("/application/listings/{$listingId}/properties/6231");

        Log::info('Listing properties: ' . print_r($properties, true));

        return $properties;
    }

    public function getListingProperties(Shop $shop, int $listingId)
    {
        $this->refreshAccessToken($shop);
        $properties = $this->client->get("/application/shops/{$shop->shop_oauth['shop_id']}/listings/{$listingId}/properties");

        Log::info('Listing properties: ' . print_r($properties, true));

        return $properties;
    }

    public function getTaxonomyProperties(Shop $shop)
    {
        $this->refreshAccessToken($shop);
        $properties = $this->client->get("/application/seller-taxonomy/nodes/{$shop->shop_oauth['default_taxonomy_id']}/properties");

        Log::info('Taxonomy properties: ' . print_r($properties, true));

        return $properties;
    }

    public function getListingInventory(Shop $shop, int $listingId)
    {
        $this->refreshAccessToken($shop);
        $shop->refresh();

        $data = (new EtsyInventoryService(
            shop: $shop,
        ))->getInventory(
            listingId: $listingId,
        );

        return $data;
    }

    public function syncListings(Shop $shop, $models): \Illuminate\Support\Collection
    {
        $listingDTOs = collect();

        try {
            $this->refreshAccessToken($shop);

            foreach ($models as $model) {
                if ($model->shopListingModel) {
                    $listingDTO = $this->updateListing($shop, $model);
                } else {
                    $listingDTO = $this->createListing($shop, $model);
                }
                $listingDTOs->push($listingDTO);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getFile() . PHP_EOL . $exception->getTraceAsString());
        }

        return $listingDTOs;
    }

    public function syncListing(Shop $shop, Model $model): ?ListingDTO
    {
        try {
            $this->refreshAccessToken($shop);

            if ($model->shopListingModel) {
                return $this->updateListing($shop, $model);
            }

            return $this->createListing($shop, $model);
        } catch (Exception $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getFile() . PHP_EOL . $exception->getTraceAsString());

            return null;
        }
    }

    public function deleteListing(Shop $shop, int $listingId): bool
    {
        return (new EtsyListingService(
            shop: $shop,
        ))->deleteListing(
            listingId: $listingId,
        );
    }

    public function getListingImages(Shop $shop, int $listingId): Collection
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return ListingImage::all(
            listing_id: $listingId,
        );
    }

    public function getShippingCarriers(Shop $shop): Collection
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return ShippingCarrier::all('NL');
    }

    private function addShopToShopOwnerShop(Shop $shop): EtsyShop|null
    {
        $etsyShop = User::getShop();

        $shopOauth = $shop->shop_oauth;
        if ($etsyShop && ! array_key_exists('shop_id', $shopOauth)) {
            $shopOauth['shop_id'] = $etsyShop->shop_id;
            $shopOauth['shop_currency'] = $etsyShop->currency_code;

            $shop->shop_oauth = $shopOauth;
            $shop->save();
        }

        return $etsyShop;
    }

    private function addShippingProfileToShopOwnerShop(Shop $shop, ?ShippingProfile $shippingProfile): void
    {
        $shopOauth = $shop->shop_oauth;
        if ((! array_key_exists('shop_shipping_profile_id', $shopOauth) && $shippingProfile) ||
            (array_key_exists('shop_shipping_profile_id', $shopOauth) && $shopOauth['shop_shipping_profile_id'] !== $shippingProfile->shipping_profile_id)
        ) {
            $shopOauth['shop_shipping_profile_id'] = $shippingProfile->shipping_profile_id;

            $shop->shop_oauth = $shopOauth;
            $shop->save();
        }
    }

    private function addReturnPolicyToShopOwnerShop(Shop $shop, ?ReturnPolicy $returnPolicy): void
    {
        $shopOauth = $shop->shop_oauth;
        if (! array_key_exists('shop_return_policy_id', $shopOauth) && $returnPolicy) {
            $shopOauth['shop_return_policy_id'] = $returnPolicy->return_policy_id;

            $shop->shop_oauth = $shopOauth;
            $shop->save();
        }
    }

    private function createListing(Shop $shop, Model $model): ListingDTO
    {
        $this->checkExistingShippingProfile(
            shopId: $shop->shop_oauth['shop_id'],
            shop: $shop,
        );
        $shop->refresh();

        $etsyListingService = new EtsyListingService(
            shop: $shop,
        );
        $listingDTO = ListingDTO::fromModel($shop, $model);
        try {
            $listing = $etsyListingService->createDraftListing($listingDTO);
        } catch (Exception $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getFile() . PHP_EOL . $exception->getTraceAsString());
        }

        if ($listing) {
            $listingDTO->listingId = $listing->listing_id;
            $listingDTO->state = $listing->state;
            $shopListingModel = (new ShopListingModelService())->createShopListingModel($shop, $model, $listingDTO);

            // Create imageDTO because needed to set listing active
            $listingImageDTO = ListingImageDTO::fromModel($shop->shop_oauth['shop_id'], $model);
            if ($listingImageDTO->image !== '') {
                $listingImageDTO->listingId = $listing->listing_id;
                $listingImage = $this->uploadListingImage($shop, $listingImageDTO);

                if ($listingImage) {
                    $shopListingModel->shop_listing_image_id = $listingImage->listing_image_id;
                    $shopListingModel->save();

                    $listingImageDTO->listingImageId = $listingImage->listing_image_id;
                    $listingDTO->listingImages = collect([$listingImage]);
                } else {
                    throw new Exception('Listing image not created: ' . print_r($listingImageDTO, true));
                }

                // Create variations for materials for listing and add inventory
                $this->updateListingInventory($shop, $listingDTO, []);

                $etsyListingService->updateListing(
                    listingId: $listing->listing_id,
                    data: [
                        'state' => EtsyListingStatesEnum::Active->value,
                    ],
                );

                $shopListingModel->state = EtsyListingStatesEnum::Active->value;
                $shopListingModel->save();

                $listingDTO->state = EtsyListingStatesEnum::Active->value;
            }
        } else {
            throw new Exception('Listing not created: ' . print_r($listingDTO, true));
        }

        return $listingDTO;
    }

    private function updateListing(Shop $shop, Model $model): ListingDTO
    {
        $etsyListingService = new EtsyListingService(
            shop: $shop,
        );
        $listing = $this->getListing($shop, $model->shopListingModel->shop_listing_id);

        $listingDTO = ListingDTO::fromModel(
            shop: $shop,
            model: $model,
            listing: $listing,
        );

        Log::info('Update listing: ' . $listingDTO->listingId);

        // Update inventory with variations, first get existing inventory so we can keep the existing intact
        $inventory = $this->getListingInventory($shop, $listingDTO->listingId);
        $this->updateListingInventory($shop, $listingDTO, $inventory);

        $materials = [];
        if ($listingDTO->materials) {
            foreach ($listingDTO->materials as $material) {
                $materials[] = str_replace('(1µm)', '(1 micron)', $material->name);
            }
        }

        // Also set state to active again
        $data = [
            'taxonomy_id' => $listingDTO->taxonomyId,
            'return_policy_id' => $listingDTO->returnPolicyId,
            'materials' => $materials,
            'item_weight' => $listingDTO->itemWeight,
            'item_length' => $listingDTO->itemLength,
            'item_width' => $listingDTO->itemWidth,
            'item_height' => $listingDTO->itemHeight,
        ];

        $etsyListingService->updateListing(
            listingId: $listing->listing_id,
            data: $data,
        );

        (new ShopListingModelService())->updateShopListingModel($model->shopListingModel, $listingDTO);

        return $listingDTO;
    }

    public function updateListingInventory(Shop $shop, ListingDTO $listingDTO, array $existingInventory): void
    {
        Log::info('Listing inventory creating: ' . $listingDTO->listingId);
        $listingId = $listingDTO->listingId;

        $variations = [];
        foreach ($listingDTO->listingInventory as $listingInventory) {
            $sku = $listingInventory->sku;
            $price = $listingInventory->price;
            $currency = $listingInventory->currency;
            $quantity = $listingInventory->quantity;
            $isEnabled = $listingInventory->isEnabled;
            $variations[] = [
                'sku' => $sku,
                'material' => str_replace('(1µm)', '(1 micron)', $listingInventory->name),
                'price' => $price,
                'quantity' => $quantity,
                'currency_code' => $currency->value,
                'is_enabled' => $isEnabled,
            ];
        }
        try {
            if (array_key_exists('products', $existingInventory)) {
                foreach ($existingInventory['products'] as $product) {
                    foreach ($product['property_values'] as $propertyValue) {
                        if ($propertyValue['property_name'] === 'Material') {
                            $offering = $product['offerings'][0];
                            $index = null;
                            for ($i = 0, $iMax = count($variations); $i < $iMax; $i++) {
                                if ($propertyValue['values'][0] === $variations[$i]['material']) {
                                    $index = $i;
                                }
                            }
                            if ($index) {
                                $variations[$index]['sku'] = $product['sku'];
                                $variations[$index]['price'] = $offering['price']['amount'] / $offering['price']['divisor'];
                                $variations[$index]['quantity'] = $offering['quantity'];
                                $variations[$index]['is_enabled'] = $offering['is_enabled'];
                            } else {
                                $variations[] = [
                                    'sku' => $product['sku'],
                                    'material' => $propertyValue['values'][0],
                                    'price' => $offering['price']['amount'] / $offering['price']['divisor'],
                                    'quantity' => $offering['quantity'],
                                    'currency_code' => $offering['price']['currency_code'],
                                    'is_enabled' => $offering['is_enabled'],
                                ];
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getTraceAsString());
        }

        try {
            $inventoryResponse = (new EtsyInventoryService(
                shop: $shop,
            ))->updateInventory(
                listingId: $listingId,
                products: $variations,
            );
            Log::info('Listing inventory created: ' . print_r($inventoryResponse, true));
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function uploadListingImage(Shop $shop, ListingImageDTO $listingImageDTO): ?ListingImage
    {
        return ListingImage::create(
            shop_id: $shop->shop_oauth['shop_id'],
            listing_id: $listingImageDTO->listingId,
            data: [
                'image' => $listingImageDTO->image,
                'rank' => $listingImageDTO->rank,
                'overwrite' => $listingImageDTO->overwrite,
                'is_watermarked' => $listingImageDTO->isWatermarked,
                'alt_text' => $listingImageDTO->altText,
            ],
        );
    }

    public function getShopPaymentAccountLedgerEntries(Shop $shop)
    {
        $this->refreshAccessToken($shop);
        new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return LedgerEntry::all(
            shop_id: $shop->shop_oauth['shop_id'],
        );
    }

    public function getShopReceipt(Shop $shop, int $receiptId)
    {
        $this->refreshAccessToken($shop);
        new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return Receipt::get(
            shop_id: $shop->shop_oauth['shop_id'],
            receipt_id: $receiptId,
        );
    }

    public function getShopReceipts(Shop $shop, array $params = [])
    {
        $this->refreshAccessToken($shop);
        new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return Receipt::all(
            shop_id: $shop->shop_oauth['shop_id'],
            params: $params,
        );
    }

    public function updateShopReceipt(Shop $shop, int $receiptId, array $data): ?Receipt
    {
        return Receipt::update(
            shop_id: $shop->shop_oath['shop_id'],
            receipt_id: $receiptId,
            data: $data,
        );
    }

    public function updateShopReceiptTracking(Shop $shop, int $receiptId, ReceiptTrackingDTO $receiptTrackingDTO): void
    {
        $this->refreshAccessToken($shop);
        new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        (new EtsyReceiptTrackingService(
            shop: $shop,
        ))->updateTracking(
            shopId: $shop->shop_oauth['shop_id'],
            receiptId: $receiptId,
            receiptTrackingDTO: $receiptTrackingDTO,
        );
    }

    public function getShopListingsFromReceipt(Shop $shop, Receipt $receipt): array
    {
        $lines = [];
        foreach ($receipt->transactions->data as $transaction) {
            $shopListingModel = ShopListingModel::with('model.materials')
                ->where('shop_id', $shop->id)
                ->where('shop_listing_id', $transaction->listing_id)
                ->where('created_at', '<=', Carbon::createFromTimestamp($receipt->created_timestamp))
                ->first();
            if ($shopListingModel) {
                foreach ($transaction->variations as $variation) {
                    $material = $shopListingModel->model->materials->where('name', $variation->formatted_value)->first();
                    if ($variation->formatted_name === 'Material' && $material) {
                        $lines[] = [
                            'transaction' => $transaction,
                            'shop_listing_model' => $shopListingModel,
                            'material' => $material,
                        ];
                    }
                }
            }
        }

        return $lines;
    }

    public function getTransactions(Shop $shop, int $listingId)
    {
        $this->refreshAccessToken($shop);
        new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return Transaction::allByListing(
            shop_id: $shop->shop_oauth['shop_id'],
            listing_id: $listingId,
        );
    }

    private function storeAccessToken(Shop $shop, array $response): Shop
    {
        $shopOauth = $shop->shop_oauth;
        $shopOauth['access_token'] = $response['access_token'];
        $shopOauth['refresh_token'] = $response['refresh_token'];

        $shop->shop_oauth = $shopOauth;
        $shop->active = true;
        $shop->save();

        return $shop;
    }
}
