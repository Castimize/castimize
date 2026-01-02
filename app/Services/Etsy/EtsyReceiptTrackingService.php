<?php

namespace App\Services\Etsy;

use App\DTO\Shops\Etsy\ReceiptTrackingDTO;
use App\Models\Shop;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Crypt;

class EtsyReceiptTrackingService
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

    public function updateTracking(int $shopId, int $receiptId, ReceiptTrackingDTO $receiptTrackingDTO)
    {
        $payload = [
            'tracking_code' => $receiptTrackingDTO->trackingCode,
            'carrier_name' => $receiptTrackingDTO->carrier,
            'send_bcc' => $receiptTrackingDTO->sendBcc,
            'note_to_buyer' => $receiptTrackingDTO->noteToBuyer,
        ];

        return $this->client->post("shops/{$shopId}/receipts/{$receiptId}/tracking", [
            'json' => $payload,
        ]);
    }
}

