<?php

declare(strict_types=1);

namespace App\Services\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ListingImageDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Enums\Etsy\EtsyListingStatesEnum;
use App\Models\Model;
use App\Models\Shop;
use App\Models\ShopListingModel;
use App\Services\Admin\ShopListingModelService;
use App\Services\Etsy\Resources\Listing;
use App\Services\Etsy\Resources\ListingVariationOption;
use Etsy\Collection;
use Etsy\Etsy;
use Etsy\OAuth\Client;
use Etsy\Resources\LedgerEntry;
use Etsy\Resources\ListingImage;
use Etsy\Resources\ListingInventory;
use Etsy\Resources\Payment;
use Etsy\Resources\Receipt;
use Etsy\Resources\ReturnPolicy;
use Etsy\Resources\SellerTaxonomy;
use Etsy\Resources\ShippingCarrier;
use Etsy\Resources\ShippingDestination;
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
    public function getRedirectUri(): string
    {
        return URL::route(
            name: 'providers.etsy.oauth',
        );
    }

    public function getAuthorizationUrl(Shop $shop): string
    {
        $client = new Client(client_id: $shop->shop_oauth['client_id']);
        $scopes = PermissionScopes::ALL_SCOPES;
//        $scopes = ['listings_d', 'listings_r', 'listings_w', 'profile_r'];

        [$verifier, $code_challenge] = $client->generateChallengeCode();
        $nonce = $client->createNonce();

        $shopOauth = $shop->shop_oauth;
        $shopOauth['verifier'] = $verifier;
        $shopOauth['nonce'] = $nonce;

        $shop->shop_oauth = $shopOauth;
        $shop->save();

        return $client->getAuthorizationUrl(
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

        $client = new Client(client_id: $shop->shop_oauth['client_id']);

        $response = $client->requestAccessToken(
            redirect_uri: $this->getRedirectUri(),
            code: $code,
            verifier: $shop->shop_oauth['verifier'],
        );

        $shop = $this->storeAccessToken($shop, $response);

        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        $etsyShop = $this->addShopToShopOwnerShop($shop);

        $shippingProfileDTO = ShippingProfileDTO::fromShop($etsyShop->shop_id);
        $shippingProfiles = $this->getShippingProfiles($shop);
        $createShippingProfile = true;
        foreach ($shippingProfiles->data as $shippingProfile) {
            if ($shippingProfile->title === $shippingProfileDTO->title) {
                $createShippingProfile = false;

                $shippingProfileDTO->shippingProfileId = $shippingProfile->shipping_profile_id;

                $this->addShippingProfileToShopOwnerShop($shop, $shippingProfile);
            }
        }

        if ($createShippingProfile) {
            $this->createShippingProfile($shop, $shippingProfileDTO);
        }
        $this->createShopReturnPolicy($shop);
    }

    public function refreshAccessToken(Shop $shop): void
    {
        if (!array_key_exists('refresh_token', $shop->shop_oauth) || !array_key_exists('access_token', $shop->shop_oauth)) {
            $shop->active = 0;
            $shop->save();
            throw new Exception(__('Shop is unauthenticated :shopOwner', ['shopOwner' => $shop->shop_owner_id]));
        }
        $client = new Client(client_id: $shop->shop_oauth['client_id']);
        $response = $client->refreshAccessToken($shop->shop_oauth['refresh_token']);
        //Log::info(print_r($response, true));

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

    public function getShippingProfile(Shop $shop)
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return ShippingProfile::get(
            shop_id: $shop->shop_oauth['shop_id'],
            profile_id: $shop->shop_oauth['shipping_profile_id'],
        );
    }

    public function getShippingProfiles(Shop $shop): Collection
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return ShippingProfile::all(
            shop_id: $shop->shop_oauth['shop_id'],
        );
    }

    public function createShippingProfile(Shop $shop, ShippingProfileDTO $shippingProfileDTO): ShippingProfileDTO
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        $shippingProfile = ShippingProfile::create(
            shop_id: $shop->shop_oauth['shop_id'],
            data: [
                'title' => $shippingProfileDTO->title,
                'origin_country_iso' => $shippingProfileDTO->originCountryIso,
                'primary_cost' => $shippingProfileDTO->primaryCost,
                'secondary_cost' => $shippingProfileDTO->secondaryCost,
                'destination_country_iso' => $shippingProfileDTO->destinationCountryIso,
                'origin_postal_code' => $shippingProfileDTO->originPostalCode,
                'min_processing_time' => $shippingProfileDTO->minProcessingTime,
                'max_processing_time' => $shippingProfileDTO->maxProcessingTime,
                'processing_time_unit' => $shippingProfileDTO->processingTimeUnit,
                'min_delivery_days' => $shippingProfileDTO->minDeliveryDays,
                'max_delivery_days' => $shippingProfileDTO->maxDeliveryDays,
            ],
        );

        $shippingProfileDTO->shippingProfileId = $shippingProfile?->shipping_profile_id;

        $this->addShippingProfileToShopOwnerShop($shop, $shippingProfile);

        return $shippingProfileDTO;
    }

    public function createShippingProfileDestination(Shop $shop, ShippingProfileDestinationDTO $shippingProfileDestinationDTO): ShippingProfileDestinationDTO
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        $shippingDestination = ShippingDestination::create(
            shop_id: $shop->shop_oauth['shop_id'],
            profile_id: $shippingProfileDestinationDTO->shippingProfileId,
            data: [
                'primary_cost' => $shippingProfileDestinationDTO->primaryCost,
                'secondary_cost' => $shippingProfileDestinationDTO->secondaryCost,
                'destination_country_iso' => $shippingProfileDestinationDTO->destinationCountryIso,
                'min_delivery_days' => $shippingProfileDestinationDTO->minDeliveryDays,
                'max_delivery_days' => $shippingProfileDestinationDTO->maxDeliveryDays,
            ]
        );

        $shippingProfileDestinationDTO->shippingProfileDestinationId = $shippingDestination->shipping_profile_destination_id;

        return $shippingProfileDestinationDTO;
    }

    public function getListing(Shop $shop, int $listingId)
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return Listing::get(listing_id: $listingId);
    }

    public function getListings(Shop $shop): Collection
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return Listing::allByShop(shop_id: $shop->shop_oauth['shop_id']);
    }

    public function syncListings(Shop $shop, $models): \Illuminate\Support\Collection
    {
        $listingDTOs = collect();
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        try {
            foreach ($models as $model) {
                if ($model->shopListingModel) {
                    $listingDTO =  $this->updateListing($shop, $model);
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
            $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

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
        return Listing::delete(listing_id: $listingId);
    }

    public function getListingImages(Shop $shop, int $listingId): Collection
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return ListingImage::all(listing_id: $listingId);
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
        if (! array_key_exists('shop_return_policy_id', $shopOauth) && $shippingProfile) {
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
        $listingDTO = ListingDTO::fromModel($shop, $model);
        $listing = $this->handleCreateDraftListing($shop, $listingDTO);

        if ($listing) {
            $listingDTO->listingId = $listing->listing_id;
            $listingDTO->state = $listing->state;
            $shopListingModel = (new ShopListingModelService())->createShopListingModel($shop, $model, $listingDTO);

            // Create imageDRTO because needed to set listing active
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

                // Update variations for materials for listing
                $this->createListingVariationOptions($shop, $listingDTO);
                $this->createListingInventory($shop, $listingDTO);

                $this->handleUpdateListing(
                    shop: $shop,
                    listingDTO: $listingDTO,
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
        $listingDTO = ListingDTO::fromModel($shop, $model);

        // Set listing to draft first to update variations
        $this->handleUpdateListing(
            shop: $shop,
            listingDTO: $listingDTO,
            data: [
                'state' => EtsyListingStatesEnum::Draft->value,
            ],
        );

        // Update variations for materials for listing
        $this->createListingVariationOptions($shop, $listingDTO);
        $this->createListingInventory($shop, $listingDTO);

        $materials = [];
        if ($listingDTO->materials) {
            foreach ($listingDTO->materials as $material) {
                $materials[] = $material->name;
            }
        }

        // Also set state to active again
        $data = [
            'title' => $listingDTO->title,
            'description' => $listingDTO->description,
            'price' => $listingDTO->price,
            'taxonomy_id' => $listingDTO->taxonomyId,
            'shipping_profile_id' => $listingDTO->shippingProfileId,
            'return_policy_id' => $listingDTO->returnPolicyId,
            'materials' => $materials,
            'item_weight' => $listingDTO->itemWeight,
            'item_length' => $listingDTO->itemLength,
            'item_width' => $listingDTO->itemWidth,
            'item_height' => $listingDTO->itemHeight,
            'state' => EtsyListingStatesEnum::Active->value,
        ];

        $this->handleUpdateListing(
            shop: $shop,
            listingDTO: $listingDTO,
            data: $data,
        );

        (new ShopListingModelService())->updateShopListingModel($model->shopListingModel, $listingDTO);

        return $listingDTO;
    }

    private function handleCreateDraftListing(Shop $shop, ListingDTO $listingDTO)
    {
        return Listing::create(
            shop_id: $shop->shop_oauth['shop_id'],
            data: [
                'quantity' => $listingDTO->quantity,
                'title' => $listingDTO->title,
                'description' => $listingDTO->description,
                'price' => $listingDTO->price,
                'who_made' => $listingDTO->whoMade,
                'when_made' => $listingDTO->whenMade,
                'taxonomy_id' => $listingDTO->taxonomyId,
                'shipping_profile_id' => $listingDTO->shippingProfileId,
                'return_policy_id' => $listingDTO->returnPolicyId,
                'materials' => $listingDTO->materials,
                'item_weight' => $listingDTO->itemWeight,
                'item_length' => $listingDTO->itemLength,
                'item_width' => $listingDTO->itemWidth,
                'item_height' => $listingDTO->itemHeight,
                'type' => 'physical',
//                'image_ids' => null,
            ],
        );
    }

    public function createListingVariationOptions(Shop $shop, ListingDTO $listingDTO)
    {
        return ListingVariationOption::create(
            listing_id: $listingDTO->listingId,
            data: [
                'property_id' => $listingDTO->listingInventory->first()->property_id,
                'formatted_values' => $listingDTO->materials->map(function ($material) {
                    return $material->name;
                })->toArray(),
                'is_available' => true,
                'visible' => true,
            ],
        );
    }

    public function createListingInventory(Shop $shop, ListingDTO $listingDTO)
    {
        $products = [];
        foreach ($listingDTO->listingInventory as $listingInventory) {
            $products[] = [
                'sku' => $listingInventory->sku,
                'property_values' => [
                    [
                        'property_id' => 515,
                        'value' => $listingInventory->name,
                    ],
                ],
                'offerings' => [
                    [
                        'price' => [
                            'amount' => $listingInventory->price * 100,
                            'currency_code' => $listingInventory->currency->value,
                        ],
                        'quantity' => 1,
                        'is_enabled' => true,
                    ],
                ],
            ];
        }

        return ListingInventory::update(
            listing_id: $listingDTO->listingId,
            data: [
                'products' => $products,
                'price_on_property' => 515,
                'quantity_on_property' => 515,
                'sku_on_property' => 515,
            ],
        );
    }

    private function saveListing(Listing $listing, array $data): Listing
    {
        return $listing->save($data);
    }

    private function handleUpdateListing(Shop $shop, ListingDTO $listingDTO, array $data)
    {
        return Listing::update(
            shop_id: $shop->shop_oauth['shop_id'],
            listing_id: $listingDTO->listingId,
            data: $data,
        );
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
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return LedgerEntry::all(
            shop_id: $shop->shop_oauth['shop_id'],
        );
    }

    public function getShopReceipts(Shop $shop, array $params = [])
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

        return Receipt::all(
            shop_id: $shop->shop_oauth['shop_id'],
            params: $params,
        );
    }

    public function getShopListingsFromReceipt(Shop $shop, Receipt $receipt): array
    {
        $lines = [];
        foreach ($receipt->transactions->data as $transaction) {
            $shopListingModel = ShopListingModel::with('model')->where('shop_id', $shop->id)->where('shop_listing_id', $transaction->listing_id)->first();
            if ($shopListingModel) {
                $lines[] = [
                    'transaction' => $transaction,
                    'shop_listing_model' => $shopListingModel,
                ];
            }
        }

        return $lines;
    }

    public function getTransactions(Shop $shop, int $listingId)
    {
        $this->refreshAccessToken($shop);
        $etsy = new Etsy($shop->shop_oauth['client_id'], $shop->shop_oauth['access_token']);

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
