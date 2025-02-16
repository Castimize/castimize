<?php

declare(strict_types=1);

namespace App\Services\Etsy;

use App\DTO\Shops\Etsy\ListingDTO;
use App\Models\Model;
use App\Models\ShopOwnerAuth;
use Etsy\Collection;
use Etsy\Etsy;
use Etsy\OAuth\Client;
use Etsy\Resources\Listing;
use Etsy\Resources\SellerTaxonomy;
use Etsy\Resources\Shop;
use Etsy\Resources\User;
use Etsy\Utils\PermissionScopes;
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

        $shop = User::getShop();

        if (! array_key_exists('shop_id', $shopOwnerAuth->shop_oauth)) {
            $shopOauth = $shopOwnerAuth->shop_oauth;
            $shopOauth['shop_id'] = $shop->shop_id;

            $shopOwnerAuth->shop_oauth = $shopOauth;
            $shopOwnerAuth->save();
        }

        return $shop;
    }

    public function getSellerTaxonomy(ShopOwnerAuth $shopOwnerAuth): Collection
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return SellerTaxonomy::all();
    }

    public function getListings(ShopOwnerAuth $shopOwnerAuth)
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        return Listing::all();
    }

    public function createListings(ShopOwnerAuth $shopOwnerAuth, $models)
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        foreach ($models as $model) {
            $this->createDraftListing($shopOwnerAuth, ListingDTO::fromModel($model));
        }
    }

    public function createListing(ShopOwnerAuth $shopOwnerAuth, Model $model): void
    {
        $this->refreshAccessToken($shopOwnerAuth);
        $etsy = new Etsy($shopOwnerAuth->shop_oauth['client_id'], $shopOwnerAuth->shop_oauth['access_token']);

        $this->createDraftListing($shopOwnerAuth, ListingDTO::fromModel($model));
    }

    private function createDraftListing(ShopOwnerAuth $shopOwnerAuth, ListingDTO $listingDTO): void
    {
        Listing::create($shopOwnerAuth->shop_oauth['shop_id'], [
            'quantity' => $listingDTO->quantity,
            'title' => $listingDTO->title,
            'description' => $listingDTO->description,
            'price' => $listingDTO->price,
            'who_made' => 'i_did',
            'when_made' => 'made_to_order',
            'taxonomy_id' => $listingDTO->taxonomyId,
            'materials' => $listingDTO->materials,
            'item_weight' => $listingDTO->itemWeight,
            'item_length' => $listingDTO->itemLength,
            'item_width' => $listingDTO->itemWidth,
            'item_height' => $listingDTO->itemHeight,
            'image_ids' => null,
        ]);
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
