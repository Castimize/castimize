<?php

declare(strict_types=1);

namespace App\Services\Etsy;

use App\Models\ShopOwnerAuth;
use Etsy\OAuth\Client;
use Illuminate\Support\Facades\URL;

class EtsyService
{
    public function getAuthorizationUrl(ShopOwnerAuth $shopOwnerAuth): string
    {
        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);
        $scopes = ['listings_d', 'listings_r', 'listings_w', 'profile_r'];

        [$verifier, $code_challenge] = $client->generateChallengeCode();

        $shopOauth = $shopOwnerAuth->shop_oauth;
        $shopOauth['verifier'] = $verifier;

        $shopOwnerAuth->shop_oauth = $shopOauth;
        $shopOwnerAuth->save();

        $nonce = $client->createNonce();

        $redirectUrl = URL::route(
            name: 'providers.etsy.oauth',
        );

        return $client->getAuthorizationUrl(
            redirect_uri: $redirectUrl,
            scope: $scopes,
            code_challenge: $code_challenge,
            nonce: $nonce,
        );
    }

    public function requestAccessToken(array $data, string $redirectUri)
    {
        $shopOwnerAuthId = decrypt($data['shop_owner_auth_id']);
        $code = $data['code'];
        $shopOwnerAuth = ShopOwnerAuth::find($shopOwnerAuthId);

        $client = new Client(client_id: $shopOwnerAuth->shop_oauth['client_id']);

        [$accessToken, $refreshToken] = $client->requestAccessToken(
            redirect_uri: $redirectUri,
            code: $code,
            verifier: $shopOwnerAuth->shop_oauth['verifier'],
        );

        $shopOwnerAuth->shop_oauth['access_token'] = $accessToken;
        $shopOwnerAuth->shop_oauth['refresh_token'] = $refreshToken;
        $shopOwnerAuth->save();
    }
}
