<?php

declare(strict_types=1);

namespace App\Services\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ListingImageDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Enums\Etsy\EtsyStatesEnum;
use App\Models\Model;
use App\Models\ShopOwnerAuth;
use App\Services\Admin\ShopListingModelService;
use App\Services\Etsy\Resources\Listing;
use Etsy\Collection;
use Etsy\Etsy;
use Etsy\OAuth\Client;
use Etsy\Resources\LedgerEntry;
use Etsy\Resources\ListingImage;
use Etsy\Resources\Payment;
use Etsy\Resources\Receipt;
use Etsy\Resources\ReturnPolicy;
use Etsy\Resources\SellerTaxonomy;
use Etsy\Resources\ShippingCarrier;
use Etsy\Resources\ShippingDestination;
use Etsy\Resources\ShippingProfile;
use Etsy\Resources\Shop;
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

    public function getAuthorizationUrl(ShopOwnerAuth $shopOwnerAuth): string
    {
        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);
        $scopes = PermissionScopes::ALL_SCOPES;
//        $scopes = ['listings_d', 'listings_r', 'listings_w', 'profile_r'];

        [$verifier, $code_challenge] = $client->generateChallengeCode();
        $nonce = $client->createNonce();

        $shopOauth = $shopOwnerAuth->shop_oauth;
        $shopOauth['verifier'] = $verifier;
        $shopOauth['nonce'] = $nonce;

        $shopOwnerAuth->shop_oauth = $shopOauth;
        $shopOwnerAuth->save();

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
        $shopOwnerAuth = ShopOwnerAuth::whereJsonContains('shop_oauth->nonce', $nonce)->first();

        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);

        $response = $client->requestAccessToken(
            redirect_uri: $this->getRedirectUri(),
            code: $code,
            verifier: $shopOwnerAuth->shop_oauth['verifier'],
        );

        $shopOwnerAuth = $this->storeAccessToken($shopOwnerAuth, $response);

        $this->addShopToShopOwnerAuth($shopOwnerAuth);
    }

    public function refreshAccessToken(ShopOwnerAuth $shopOwnerAuth): void
    {
        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);
        $response = $client->refreshAccessToken($shopOwnerAuth->shop_oauth['refresh_token']);
        //Log::info(print_r($response, true));

        $this->storeAccessToken($shopOwnerAuth, $response);
    }

    public function getShop(ShopOwnerAuth $shopOwnerAuth): Shop|null
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return $this->addShopToShopOwnerAuth($shopOwnerAuth);
    }

    public function getShopReturnPolicy(ShopOwnerAuth $shopOwnerAuth, int $returnPolicyId): ReturnPolicy
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return ReturnPolicy::get(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
            policy_id: $returnPolicyId,
        );
    }

    public function getShopReturnPolicies(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return ReturnPolicy::all(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
        );
    }

    public function createShopReturnPolicy(ShopOwnerAuth $shopOwnerAuth): ReturnPolicy
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        $shopReturnPolicy = ReturnPolicy::create(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
            data: [
                'accepts_returns' => false,
                'accepts_exchanges' => false,
                'return_deadline' => null,
            ],
        );

        $this->addReturnPolicyToShopOwnerAuth($shopOwnerAuth, $shopReturnPolicy);

        return $shopReturnPolicy;
    }

    public function getSellerTaxonomy(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return SellerTaxonomy::all();
    }

    public function getShippingProfile(ShopOwnerAuth $shopOwnerAuth)
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return ShippingProfile::get(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
            profile_id: $shopOwnerAuth->shop_oauth['shipping_profile_id'],
        );
    }

    public function getShippingProfiles(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return ShippingProfile::all(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
        );
    }

    public function createShippingProfile(ShopOwnerAuth $shopOwnerAuth, ShippingProfileDTO $shippingProfileDTO): ShippingProfileDTO
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        $shippingProfile = ShippingProfile::create(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
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

        return $shippingProfileDTO;
    }

    public function createShippingProfileDestination(ShopOwnerAuth $shopOwnerAuth, ShippingProfileDestinationDTO $shippingProfileDestinationDTO): ShippingProfileDestinationDTO
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        $shippingDestination = ShippingDestination::create(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
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

    public function getListings(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return Listing::allByShop(shop_id: $shopOwnerAuth->shop_oauth['shop_id']);
    }

    public function syncListings(ShopOwnerAuth $shopOwnerAuth, $models): \Illuminate\Support\Collection
    {
        $listingDTOs = collect();
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        try {
            foreach ($models as $model) {
                $listingDTO = $this->createListing($shopOwnerAuth, $model);
                $listingDTOs->push($listingDTO);
//                dd($listingDTO);
            }
        } catch (Exception $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getFile() . PHP_EOL . $exception->getTraceAsString());
        }

        return $listingDTOs;
    }

    public function syncListing(ShopOwnerAuth $shopOwnerAuth, Model $model): ListingDTO
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return $this->createListing($shopOwnerAuth, $model);
    }

    public function deleteListing(ShopOwnerAuth $shopOwnerAuth, int $listingId): bool
    {
        return Listing::delete(listing_id: $listingId);
    }

    public function getShippingCarriers(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return ShippingCarrier::all('NL');
    }

    private function addShopToShopOwnerAuth(ShopOwnerAuth $shopOwnerAuth): Shop|null
    {
        $shop = User::getShop();

        $shopOauth = $shopOwnerAuth->shop_oauth;
        if ($shop && ! array_key_exists('shop_id', $shopOauth)) {
            $shopOauth['shop_id'] = $shop->shop_id;
            $shopOauth['shop_currency'] = $shop->currency_code;

            $shopOwnerAuth->shop_oauth = $shopOauth;
            $shopOwnerAuth->save();
        }

        return $shop;
    }

    private function addReturnPolicyToShopOwnerAuth(ShopOwnerAuth $shopOwnerAuth, ReturnPolicy $returnPolicy)
    {
        $shopOauth = $shopOwnerAuth->shop_oauth;
        if (! array_key_exists('shop_return_policy_id', $shopOauth)) {
            $shopOauth['shop_return_policy_id'] = $returnPolicy->return_policy_id;

            $shopOwnerAuth->shop_oauth = $shopOauth;
            $shopOwnerAuth->save();
        }
    }

    private function createListing(ShopOwnerAuth $shopOwnerAuth, Model $model): ListingDTO
    {
        $listingDTO = ListingDTO::fromModel($shopOwnerAuth, $model);
        $listing = $this->createDraftListing($shopOwnerAuth, $listingDTO);

        if ($listing) {
            $listingDTO->listingId = $listing->listing_id;
            $listingDTO->state = $listing->state;
            $shopListingModel = (new ShopListingModelService())->createShopListingModel($shopOwnerAuth, $model, $listingDTO);

            $listingImageDTO = ListingImageDTO::fromModel($shopOwnerAuth->shop_oauth['shop_id'], $model);
            if ($listingImageDTO->image !== '') {
                $listingImageDTO->listingId = $listing->listing_id;
                $listingImage = $this->uploadListingImage($shopOwnerAuth, $listingImageDTO);

                if ($listingImage) {
                    $shopListingModel->shop_listing_image_id = $listingImage->listing_image_id;
                    $shopListingModel->save();

                    $listingImageDTO->listingImageId = $listingImage->listing_image_id;
                    $listingDTO->listingImages = collect([$listingImage]);
                } else {
                    throw new Exception('Listing image not created: ' . print_r($listingImageDTO, true));
                }

                $this->updateListing(
                    shopOwnerAuth: $shopOwnerAuth,
                    listingDTO: $listingDTO,
                    data: [
                        'state' => EtsyStatesEnum::Active->value,
                    ],
                );

                $shopListingModel->state = EtsyStatesEnum::Active->value;
                $shopListingModel->save();

                $listingDTO->state = EtsyStatesEnum::Active->value;
            }
        } else {
            throw new Exception('Listing not created: ' . print_r($listingDTO, true));
        }

        return $listingDTO;
    }

    private function createDraftListing(ShopOwnerAuth $shopOwnerAuth, ListingDTO $listingDTO)
    {
        return Listing::create(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
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

    private function saveListing(Listing $listing, array $data): Listing
    {
        return $listing->save($data);
    }

    private function updateListing(ShopOwnerAuth $shopOwnerAuth, ListingDTO $listingDTO, array $data)
    {
        return Listing::update(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
            listing_id: $listingDTO->listingId,
            data: $data,
        );
    }

    public function uploadListingImage(ShopOwnerAuth $shopOwnerAuth, ListingImageDTO $listingImageDTO): ?ListingImage
    {
        return ListingImage::create(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
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

    public function getShopPaymentAccountLedgerEntries(ShopOwnerAuth $shopOwnerAuth)
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return LedgerEntry::all(
            shop_id: $shopOwnerAuth->shop_oauth['shop_id'],
        );
    }

    private function storeAccessToken(ShopOwnerAuth $shopOwnerAuth, array $response): ShopOwnerAuth
    {
        $shopOauth = $shopOwnerAuth->shop_oauth;
        $shopOauth['access_token'] = $response['access_token'];
        $shopOauth['refresh_token'] = $response['refresh_token'];

        $shopOwnerAuth->shop_oauth = $shopOauth;
        $shopOwnerAuth->active = true;
        $shopOwnerAuth->save();

        return $shopOwnerAuth;
    }
}
