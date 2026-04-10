<?php

namespace App\Services\Etsy;

use App\Models\Shop;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class EtsyReadinessStateService
{
    protected Client $client;

    public function __construct(
        protected Shop $shop,
    ) {
        $this->client = new Client([
            'base_uri' => 'https://openapi.etsy.com/v3/application/',
            'headers' => [
                'Authorization' => 'Bearer '.$this->shop->shop_oauth['access_token'],
                'x-api-key' => $this->shop->shop_oauth['client_id'].':'.config('services.shops.etsy.client_secret'),
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Creates a readiness state definition for the shop.
     * If it already exists, retrieves the ID from the Conflict response.
     *
     * @return int|null The readiness state definition ID, or null on failure.
     */
    public function createReadinessStateDefinition(
        string $readinessState = 'made_to_order',
        int $minProcessingTime = 1,
        int $maxProcessingTime = 10,
        string $processingTimeUnit = 'days',
    ): ?int {
        $shopId = $this->shop->shop_oauth['shop_id'];

        try {
            $response = $this->client->post("shops/{$shopId}/readiness-state-definitions", [
                'form_params' => [
                    'readiness_state' => $readinessState,
                    'min_processing_time' => $minProcessingTime,
                    'max_processing_time' => $maxProcessingTime,
                    'processing_time_unit' => $processingTimeUnit,
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return $data['readiness_state_definition_id'] ?? $data['id'] ?? null;
        } catch (ClientException $e) {
            if ($e->getResponse()->getStatusCode() === 409) {
                // Definition already exists — retrieve ID from Content-Location header
                $location = $e->getResponse()->getHeaderLine('Content-Location');
                if ($location) {
                    return $this->getReadinessStateDefinitionIdFromLocation($location);
                }
            }

            Log::error($e->getMessage().PHP_EOL.$e->getFile().PHP_EOL.$e->getTraceAsString());

            return null;
        } catch (Exception $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getFile().PHP_EOL.$e->getTraceAsString());

            return null;
        }
    }

    private function getReadinessStateDefinitionIdFromLocation(string $location): ?int
    {
        try {
            $response = $this->client->get($location);
            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            return $data['readiness_state_definition_id'] ?? $data['id'] ?? null;
        } catch (Exception $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getFile().PHP_EOL.$e->getTraceAsString());

            return null;
        }
    }
}
