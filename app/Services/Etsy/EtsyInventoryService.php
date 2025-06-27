<?php

namespace App\Services\Etsy;

use App\Models\Shop;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class EtsyInventoryService
{
    protected Client $client;

    public function __construct(
        protected Shop $shop,
    ) {
        $this->client = new Client([
            'base_uri' => 'https://openapi.etsy.com/v3/application/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->shop->shop_oauth['access_token'],
                'x-api-key' => $this->shop->shop_oauth['client_id'],
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getInventory(int $listingId)
    {
        try {
            $response = $this->client->get("listings/{$listingId}/inventory");

            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getTraceAsString());
            return [];
        }
    }

    public function updateInventory(int $listingId, array $products)
    {
        $inventoryProducts = [];

        foreach ($products as $product) {
            $inventoryProducts[] = [
                'sku' => $product['sku'],
                'property_values' => [
                    [
                        'property_id' => 514,
                        'property_name' => 'Material',
                        'values' => [$product['material']],
                    ],
                ],
                'offerings' => [
                    [
                        'price' => $product['price'],
                        'quantity' => $product['quantity'],
                        'is_enabled' => true,
                    ],
                ],
            ];
        }

        try {
            $payload = [
                'products' => $inventoryProducts,
                'price_on_property' => [514],
                'quantity_on_property' => [514],
                'sku_on_property' => [514],
            ];
//            dd($payload);
            return $this->client->put("listings/{$listingId}/inventory", [
                'json' => $payload,
            ]);
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}

