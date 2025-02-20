<?php

declare(strict_types=1);

namespace App\Services\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ListingImageDTO;
use App\Models\Model;
use App\Models\ShopOwnerAuth;
use App\Services\Admin\ShopListingModelService;
use Etsy\Collection;
use Etsy\Etsy;
use Etsy\OAuth\Client;
use Etsy\Resources\Listing;
use Etsy\Resources\ListingImage;
use Etsy\Resources\SellerTaxonomy;
use Etsy\Resources\ShippingCarrier;
use Etsy\Resources\Shop;
use Etsy\Resources\User;
use Etsy\Utils\PermissionScopes;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class EtsyService
{
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

        $this->storeAccessToken($shopOwnerAuth, $response);

        $this->addShopToShopOwnerAuth($shopOwnerAuth);
    }

    public function refreshAccessToken(ShopOwnerAuth $shopOwnerAuth): void
    {
        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);
        $response = $client->refreshAccessToken($shopOwnerAuth->shop_oauth['refresh_token']);
        Log::info(print_r($response, true));

        $this->storeAccessToken($shopOwnerAuth, $response);
    }

    public function getShop(ShopOwnerAuth $shopOwnerAuth): Shop|null
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return $this->addShopToShopOwnerAuth($shopOwnerAuth);
    }

    public function getSellerTaxonomy(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return SellerTaxonomy::all();
    }

    public function getListings(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return Listing::all();
    }

    public function syncListings(ShopOwnerAuth $shopOwnerAuth, $models): \Illuminate\Support\Collection
    {
        $listingDTOs = collect();
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        foreach ($models as $model) {
            $listingDTO = $this->createListing($shopOwnerAuth, $model);
            $listingDTOs->push($listingDTO);
        }

        return $listingDTOs;
    }

    public function syncListing(ShopOwnerAuth $shopOwnerAuth, Model $model): ListingDTO
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return $this->createListing($shopOwnerAuth, $model);
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

        if (! array_key_exists('shop_id', $shopOwnerAuth->shop_oauth)) {
            $shopOauth = $shopOwnerAuth->shop_oauth;
            $shopOauth['shop_id'] = $shop->shop_id;

            $shopOwnerAuth->shop_oauth = $shopOauth;
            $shopOwnerAuth->save();
        }

        return $shop;
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
            $listingImage = $this->uploadListingImage($shopOwnerAuth, $listingImageDTO);

            if ($listingImage) {
                $shopListingModel->shop_listing_image_id = $listingImage->listing_image_id;
                $shopListingModel->save();

                $listingImageDTO->listingImageId = $listingImage->listing_image_id;
                $listingDTO->listingImages = collect([$listingImage]);
            }
        } else {
            throw new Exception('Listing not created: ' . print_r($listing, true));
        }

        return $listingDTO;
    }

    private function createDraftListing(ShopOwnerAuth $shopOwnerAuth, ListingDTO $listingDTO): ?Listing
    {
        return Listing::create(
            $shopOwnerAuth->shop_oauth['shop_id'], [
            'quantity' => $listingDTO->quantity,
            'title' => $listingDTO->title,
            'description' => $listingDTO->description,
            'price' => $listingDTO->price,
            'who_made' => $listingDTO->whoMade,
            'when_made' => $listingDTO->whenMade,
            'taxonomy_id' => $listingDTO->taxonomyId,
            'materials' => $listingDTO->materials,
            'item_weight' => $listingDTO->itemWeight,
            'item_length' => $listingDTO->itemLength,
            'item_width' => $listingDTO->itemWidth,
            'item_height' => $listingDTO->itemHeight,
            'image_ids' => null,
        ]);
    }

    private function updateListing(ShopOwnerAuth $shopOwnerAuth, ListingDTO $listingDTO, array $data): ?Listing
    {
        return Listing::update(
            $shopOwnerAuth->shop_oauth['shop_id'],
            $listingDTO->listingId,
            $data
        );
    }

    private function uploadListingImage(ShopOwnerAuth $shopOwnerAuth, ListingImageDTO $listingImageDTO): ?ListingImage
    {
        return ListingImage::create(
            $shopOwnerAuth->shop_oauth['shop_id'],
            $listingImageDTO->listingId, [
                'image' => $listingImageDTO->image,
                'rank' => $listingImageDTO->rank,
                'overwrite' => $listingImageDTO->overwrite,
                'is_watermarked' => $listingImageDTO->isWatermarked,
                'alt_text' => $listingImageDTO->altText,
            ]
        );
    }

    public function getRedirectUri(): string
    {
        return URL::route(
            name: 'providers.etsy.oauth',
        );
    }

    private function storeAccessToken(ShopOwnerAuth $shopOwnerAuth, array $response): void
    {
        $shopOauth = $shopOwnerAuth->shop_oauth;
        $shopOauth['access_token'] = $response['access_token'];
        $shopOauth['refresh_token'] = $response['refresh_token'];

        $shopOwnerAuth->shop_oauth = $shopOauth;
        $shopOwnerAuth->active = true;
        $shopOwnerAuth->save();
    }
}
