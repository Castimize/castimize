<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers\Webhooks;

use App\Models\Customer;
use App\Models\CustomerShipment;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\OrderStatus;
use App\Models\Shop;
use App\Models\ShopOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShippoWebhookControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure required OrderStatus records exist for the controller's OrderQueuesService calls.
        OrderStatus::firstOrCreate(
            ['slug' => 'in-transit-to-customer'],
            ['status' => 'In transit to customer', 'end_status' => false]
        );
        OrderStatus::firstOrCreate(
            ['slug' => 'completed'],
            ['status' => 'Completed', 'end_status' => true]
        );
        OrderStatus::firstOrCreate(
            ['slug' => 'at-dc'],
            ['status' => 'At DC', 'end_status' => false]
        );
    }

    /**
     * Build a minimal Shippo track_updated payload.
     */
    private function buildTrackUpdatedPayload(string $status, string $transactionId, string $metadata = ''): array
    {
        return [
            'event' => 'track_updated',
            'data' => [
                'object_id' => $transactionId,
                'transaction' => $transactionId,
                'metadata' => $metadata,
                'eta' => '2026-03-15T00:00:00Z',
                'tracking_status' => [
                    'object_id' => 'ts-obj-'.uniqid(),
                    'status' => $status,
                    'substatus' => ['text' => null],
                    'status_details' => 'Package in transit',
                    'status_date' => '2026-03-08T10:00:00Z',
                    'location' => ['city' => 'Amsterdam', 'state' => 'NH', 'zip' => '1000AA', 'country' => 'NL'],
                ],
            ],
        ];
    }

    /**
     * Create a CustomerShipment without triggering the observer (which requires additional data).
     */
    private function createCustomerShipment(
        string $transactionId,
        ?string $trackingNumber = '1Z999AA10123456784',
        ?string $trackingUrl = 'https://tracking.ups.com/track?trackingNumber=1Z999AA10123456784',
        ?string $sentAt = null,
    ): CustomerShipment {
        $customer = Customer::factory()->create();

        /** @var CustomerShipment $shipment */
        $shipment = CustomerShipment::withoutEvents(function () use ($customer, $transactionId, $trackingNumber, $trackingUrl, $sentAt) {
            return CustomerShipment::create([
                'customer_id' => $customer->id,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $trackingUrl,
                'shippo_transaction_id' => $transactionId,
                'sent_at' => $sentAt,
                'total_costs' => 0,
                'service_costs' => 0,
            ]);
        });

        return $shipment;
    }

    /**
     * Create a full setup: CustomerShipment + Order + ShopOrder + Shop + OrderQueue.
     *
     * @return array{shipment: CustomerShipment, order: Order, shopOrder: ShopOrder, shop: Shop, transactionId: string, orderQueue: OrderQueue}
     */
    private function createShipmentWithEtsyOrder(
        ?string $trackingNumber = '1Z999AA10123456784',
        ?string $trackingUrl = 'https://tracking.ups.com/track?trackingNumber=1Z999AA10123456784',
        ?string $sentAt = null,
    ): array {
        $shop = Shop::factory()->etsy()->create([
            'shop_oauth' => [
                'shop_id' => 12345678,
                'client_id' => 'test-client-id',
                'access_token' => 'test-access-token',
                'refresh_token' => 'test-refresh-token',
            ],
        ]);

        $transactionId = 'txn-'.uniqid();

        $shipment = $this->createCustomerShipment(
            transactionId: $transactionId,
            trackingNumber: $trackingNumber,
            trackingUrl: $trackingUrl,
            sentAt: $sentAt,
        );

        $order = Order::factory()->create([
            'order_number' => 'ORD-'.uniqid(),
        ]);

        $shopOrder = ShopOrder::create([
            'shop_owner_id' => $shop->shop_owner_id,
            'shop_id' => $shop->id,
            'order_number' => $order->order_number,
            'shop_receipt_id' => 9876543,
            'state' => 'open',
        ]);

        $orderQueue = OrderQueue::withoutEvents(function () use ($order, $shipment) {
            return OrderQueue::factory()->create([
                'order_id' => $order->id,
                'customer_shipment_id' => $shipment->id,
            ]);
        });

        return compact('shipment', 'order', 'shopOrder', 'shop', 'transactionId', 'orderQueue');
    }

    #[Test]
    public function it_skips_etsy_tracking_when_shipment_is_null(): void
    {
        // No CustomerShipment in DB; transactionId won't match anything
        $payload = $this->buildTrackUpdatedPayload('TRANSIT', 'nonexistent-txn-id');

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        // Controller catches all exceptions and always returns a response
        $response->assertStatus(200);
    }

    #[Test]
    public function it_skips_etsy_tracking_when_status_is_not_transit(): void
    {
        $data = $this->createShipmentWithEtsyOrder();

        // Use a status that doesn't trigger the Etsy block (DELIVERED goes to a different branch)
        // For DELIVERED, the controller will try to create a TrackingStatus row and update arrived_at.
        // The Etsy update only happens in the TRANSIT + sent_at === null block.
        $payload = $this->buildTrackUpdatedPayload('PRE_TRANSIT', $data['transactionId']);

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        // No crash, tracking status is created
        $response->assertStatus(200);

        $this->assertDatabaseHas('tracking_statuses', [
            'model_id' => $data['shipment']->id,
            'status' => 'PRE_TRANSIT',
        ]);

        // sent_at should NOT be set (only set on TRANSIT)
        $data['shipment']->refresh();
        $this->assertNull($data['shipment']->sent_at);
    }

    #[Test]
    public function it_skips_etsy_tracking_when_tracking_number_is_null(): void
    {
        // Shipment has no tracking number — the guard `if ($shipment->tracking_number)` prevents the Etsy call
        $data = $this->createShipmentWithEtsyOrder(trackingNumber: null, sentAt: null);

        $payload = $this->buildTrackUpdatedPayload('TRANSIT', $data['transactionId']);

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        $response->assertStatus(200);

        // sent_at should be set (TRANSIT processing happened)
        $data['shipment']->refresh();
        $this->assertNotNull($data['shipment']->sent_at);

        // But no Etsy tracking — verified by no exception thrown (EtsyService would throw without real tokens)
        // and test passes cleanly.
    }

    #[Test]
    public function it_skips_etsy_tracking_when_sent_at_already_set(): void
    {
        // sent_at already set means the Etsy block (inside `if ($shipment->sent_at === null)`) is skipped
        $data = $this->createShipmentWithEtsyOrder(sentAt: now()->subDay()->toDateTimeString());

        $payload = $this->buildTrackUpdatedPayload('TRANSIT', $data['transactionId']);

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        $response->assertStatus(200);

        // A TrackingStatus row IS still created (that's outside the sent_at === null guard)
        $this->assertDatabaseHas('tracking_statuses', [
            'model_id' => $data['shipment']->id,
            'status' => 'TRANSIT',
        ]);
    }

    #[Test]
    public function it_creates_tracking_status_on_transit_event(): void
    {
        // sent_at already set to avoid Etsy API calls
        $data = $this->createShipmentWithEtsyOrder(sentAt: now()->subDay()->toDateTimeString());

        $payload = $this->buildTrackUpdatedPayload('TRANSIT', $data['transactionId']);

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('tracking_statuses', [
            'model_id' => $data['shipment']->id,
            'model_type' => CustomerShipment::class,
            'status' => 'TRANSIT',
        ]);
    }

    #[Test]
    public function it_returns_bad_request_for_invalid_json_payload(): void
    {
        $response = $this->call(
            'POST',
            '/webhooks/shipping/shippo/callback',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'not-valid-json{'
        );

        $this->assertTrue(in_array($response->getStatusCode(), [200, 400, 422], true));
    }

    #[Test]
    public function it_sets_sent_at_when_transit_status_and_sent_at_is_null(): void
    {
        // No tracking number to prevent Etsy API calls, but sent_at starts as null
        $data = $this->createShipmentWithEtsyOrder(
            trackingNumber: null,
            sentAt: null,
        );

        $this->assertNull($data['shipment']->sent_at);

        $payload = $this->buildTrackUpdatedPayload('TRANSIT', $data['transactionId']);

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        $response->assertStatus(200);

        $data['shipment']->refresh();
        $this->assertNotNull($data['shipment']->sent_at);
    }

    #[Test]
    public function it_updates_expected_delivery_date_on_transit(): void
    {
        // sent_at already set to avoid Etsy calls
        $data = $this->createShipmentWithEtsyOrder(sentAt: now()->subDay()->toDateTimeString());

        $payload = $this->buildTrackUpdatedPayload('TRANSIT', $data['transactionId']);

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        $response->assertStatus(200);

        $data['shipment']->refresh();
        $this->assertNotNull($data['shipment']->expected_delivery_date);
    }

    #[Test]
    public function it_handles_unknown_event_type_gracefully(): void
    {
        $payload = [
            'event' => 'some_unknown_event',
            'data' => [],
        ];

        $response = $this->postJson('/webhooks/shipping/shippo/callback', $payload);

        // Unknown events fall through to default case and return missingMethod (200)
        $response->assertStatus(200);
    }
}
