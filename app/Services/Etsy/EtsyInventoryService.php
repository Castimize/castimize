<?php

namespace App\Services\Etsy;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class EtsyInventoryService
{
    protected Client $client;

    public function __construct(
        protected string $clientId,
        protected string $accessToken,
    ) {
        $this->client = new Client([
            'base_uri' => 'https://openapi.etsy.com/v3/application/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
                'x-api-key' => $this->clientId,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getInventory(int $listingId): ?array
    {
        try {
            $response = $this->client->get("listings/{$listingId}/inventory");
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (GuzzleException $e) {
            Log::error($e);
            return null;
        }
    }

    public function updateInventory(int $listingId, array $products): bool
    {
        try {
            $payload = [
                'products' => $products,
                'price_on_property' => [513],     // 513 = Custom property #1 (bijv. materiaal)
                'quantity_on_property' => [513],
                'sku_on_property' => [513],
            ];

            $response = $this->client->put("listings/{$listingId}/inventory", [
                'json' => $payload,
            ]);

            return $response->getStatusCode() === 200;
        } catch (GuzzleException $e) {
            Log::error($e);
            report($e);
            return false;
        }
    }
}
