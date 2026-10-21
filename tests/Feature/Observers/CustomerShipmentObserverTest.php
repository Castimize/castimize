<?php

declare(strict_types=1);

namespace Tests\Feature\Observers;

use App\Models\CustomerShipment;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\Upload;
use App\Services\Admin\ShippingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerShipmentObserverTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @return array<string, mixed>
     */
    private function shippoResponse(): array
    {
        return [
            'shipment' => ['object_id' => 'shp_1'],
            'transaction' => [
                'object_id' => 'trx_1',
                'status' => 'SUCCESS',
                'tracking_number' => 'TRACK1',
                'tracking_url_provider' => 'https://track.test/TRACK1',
                'label_url' => 'https://label.test/1',
                'commercial_invoice_url' => 'https://invoice.test/1',
                'qr_code_url' => null,
            ],
            'request' => [
                'parcel' => ['length' => 17, 'width' => 13],
                'customs_items' => [['description' => 'Cast metal', 'quantity' => 1]],
                'customs_declaration' => ['importer_reference' => '9618', 'items' => ['itm_1']],
                'shipment' => ['address_to' => ['city' => 'Draper'], 'extra' => ['reference_1' => '9618']],
                'transaction' => ['rate' => 'rate_1', 'metadata' => 'customer_shipment:1'],
            ],
        ];
    }

    private function createOrderQueue(): OrderQueue
    {
        return Model::withoutEvents(function (): OrderQueue {
            $order = Order::factory()->create();
            $upload = Upload::factory()->create(['order_id' => $order->id]);

            return OrderQueue::factory()->create(['order_id' => $order->id, 'upload_id' => $upload->id]);
        });
    }

    private function createShipment(OrderQueue $orderQueue): CustomerShipment
    {
        $shipment = new CustomerShipment;
        $shipment->selectedPOs = json_encode([$orderQueue->id]);
        $shipment->from_address_name = 'Castimize B.V.';
        $shipment->to_address_name = 'Justin Maughan';
        $shipment->parcel_length = 17;
        $shipment->save();

        return $shipment;
    }

    #[Test]
    public function it_stores_the_shippo_request_data_on_the_shipment(): void
    {
        $orderQueue = $this->createOrderQueue();
        $shippingService = Mockery::mock(ShippingService::class);
        $shippingService->shouldReceive('createShippoCustomerShipment')->once()->andReturn($this->shippoResponse());
        $this->app->instance(ShippingService::class, $shippingService);

        $shipment = $this->createShipment($orderQueue)->fresh();

        $this->assertSame('trx_1', $shipment->shippo_transaction_id);
        $this->assertEquals($this->shippoResponse()['request'], $shipment->shippo_request_data);
        $this->assertSame('customer_shipment:1', $shipment->shippo_request_data['transaction']['metadata']);
    }

    #[Test]
    public function it_leaves_the_request_data_empty_when_the_response_has_none(): void
    {
        $response = $this->shippoResponse();
        unset($response['request']);
        $orderQueue = $this->createOrderQueue();
        $shippingService = Mockery::mock(ShippingService::class);
        $shippingService->shouldReceive('createShippoCustomerShipment')->once()->andReturn($response);
        $this->app->instance(ShippingService::class, $shippingService);

        $shipment = $this->createShipment($orderQueue)->fresh();

        $this->assertSame('trx_1', $shipment->shippo_transaction_id);
        $this->assertNull($shipment->shippo_request_data);
    }
}
