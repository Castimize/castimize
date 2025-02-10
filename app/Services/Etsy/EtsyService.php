<?php

declare(strict_types=1);

namespace App\Services\Etsy;

use App\Models\ShopOwnerAuth;
use Etsy\OAuth\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class EtsyService
{
    public function getAuthorizationUrl(ShopOwnerAuth $shopOwnerAuth): string
    {
        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);
        $scopes = ['listings_d', 'listings_r', 'listings_w', 'profile_r'];

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
        var_dump($shopOwnerAuth->shop_oauth);

        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);

        [$accessToken, $refreshToken] = $client->requestAccessToken(
            redirect_uri: $this->getRedirectUri(),
            code: $code,
            verifier: $shopOwnerAuth->shop_oauth['verifier'],
        );

        $shopOwnerAuth->shop_oauth['access_token'] = $accessToken;
        $shopOwnerAuth->shop_oauth['refresh_token'] = $refreshToken;
        $shopOwnerAuth->save();
    }

    public function getRedirectUri(): string
    {
        return URL::route(
            name: 'providers.etsy.oauth',
        );
    }
}
