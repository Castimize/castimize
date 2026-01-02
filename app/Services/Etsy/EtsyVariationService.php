<?php

namespace App\Services\Etsy;

use App\Models\Material;
use App\Models\Shop;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EtsyVariationService
{
    protected Client $client;

    public function __construct(
        protected Shop $shop,
    ) {
        $this->client = new Client([
            'base_uri' => 'https://openapi.etsy.com/v3/application/',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->shop->shop_oauth['access_token'],
//                'x-api-key' => $this->shop->shop_oauth['client_id'],
                'x-api-key' => $this->shop->shop_oauth['client_id'] . ':' . config('services.shops.etsy.client_secret'),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function getVariationOptions(int $listingId): array|null
    {
        try {
            $response = $this->client->get("listings/{$listingId}/variation-options");
            return json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            return null;
        }
    }

    public function createVariationOptions(int $listingId)
    {
        $materials = Material::all(['name'])->pluck('name')->toArray();

        $payload = [
            [
                'property_id' => 514, // Custom property
                'value_options' => $materials,
            ],
        ];

        try {
            $response = $this->client->put("listings/{$listingId}/variation-options", [
                'json' => $payload,
            ]);
            return $response->getBody()->getContents();
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getTraceAsString());
        }
    }

    public function getValueIdsForMaterial(int $listingId): array
    {
        try {
            $response = $this->client->get("listings/{$listingId}/variation-options");
        } catch (Exception $e) {
            return [];
        }

        $materials = [];

        foreach ($response as $option) {
            if ($option['property_id'] === 514) {
                foreach ($option['values'] as $value) {
                    $materials[$value['value']] = $value['value_id'];
                }
            }
        }

        return $materials;
    }
}
