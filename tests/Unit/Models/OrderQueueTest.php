<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\CustomerShipment;
use App\Models\Manufacturer;
use App\Models\ManufacturerCost;
use App\Models\ManufacturerShipment;
use App\Models\Order;
use App\Models\OrderQueue;
use App\Models\OrderQueueStatus;
use App\Models\Rejection;
use App\Models\Reprint;
use App\Models\ShippingFee;
use App\Models\Upload;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderQueueTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_uses_custom_table_name(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertEquals('order_queue', $orderQueue->getTable());
    }

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $orderQueue = new OrderQueue;
        $fillable = $orderQueue->getFillable();

        $this->assertContains('manufacturer_id', $fillable);
        $this->assertContains('upload_id', $fillable);
        $this->assertContains('order_id', $fillable);
        $this->assertContains('due_date', $fillable);
        $this->assertContains('manufacturer_costs', $fillable);
        $this->assertContains('remarks', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $orderQueue = new OrderQueue;
        $casts = $orderQueue->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['due_date']);
        $this->assertEquals('datetime', $casts['final_arrival_date']);
        $this->assertEquals('datetime', $casts['contract_date']);
    }

    #[Test]
    public function it_casts_booleans_correctly(): void
    {
        $orderQueue = new OrderQueue;
        $casts = $orderQueue->getCasts();

        $this->assertEquals('boolean', $casts['status_manual_changed']);
    }

    #[Test]
    public function it_converts_manufacturer_costs_from_cents(): void
    {
        $orderQueue = new OrderQueue;
        $orderQueue->setRawAttributes(['manufacturer_costs' => 5000]);

        $this->assertEquals(50.00, $orderQueue->manufacturer_costs);
    }

    #[Test]
    public function it_belongs_to_manufacturer(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(BelongsTo::class, $orderQueue->manufacturer());
        $this->assertEquals(Manufacturer::class, $orderQueue->manufacturer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_upload(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(BelongsTo::class, $orderQueue->upload());
        $this->assertEquals(Upload::class, $orderQueue->upload()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_order(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(BelongsTo::class, $orderQueue->order());
        $this->assertEquals(Order::class, $orderQueue->order()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_shipping_fee(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(BelongsTo::class, $orderQueue->shippingFee());
        $this->assertEquals(ShippingFee::class, $orderQueue->shippingFee()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_manufacturer_shipment(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(BelongsTo::class, $orderQueue->manufacturerShipment());
        $this->assertEquals(ManufacturerShipment::class, $orderQueue->manufacturerShipment()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_manufacturer_cost(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(BelongsTo::class, $orderQueue->manufacturerCost());
        $this->assertEquals(ManufacturerCost::class, $orderQueue->manufacturerCost()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_customer_shipment(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(BelongsTo::class, $orderQueue->customerShipment());
        $this->assertEquals(CustomerShipment::class, $orderQueue->customerShipment()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_order_queue_statuses(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(HasMany::class, $orderQueue->orderQueueStatuses());
        $this->assertEquals(OrderQueueStatus::class, $orderQueue->orderQueueStatuses()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_latest_order_queue_status(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(HasOne::class, $orderQueue->latestOrderQueueStatus());
        $this->assertEquals(OrderQueueStatus::class, $orderQueue->latestOrderQueueStatus()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_rejection(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(HasOne::class, $orderQueue->rejection());
        $this->assertEquals(Rejection::class, $orderQueue->rejection()->getRelated()::class);
    }

    #[Test]
    public function it_has_one_reprint(): void
    {
        $orderQueue = new OrderQueue;

        $this->assertInstanceOf(HasOne::class, $orderQueue->reprint());
        $this->assertEquals(Reprint::class, $orderQueue->reprint()->getRelated()::class);
    }

    #[Test]
    public function it_returns_default_values_in_get_overview_item_when_upload_is_null(): void
    {
        $orderQueue = new OrderQueue;
        $orderQueue->id = 123;
        $orderQueue->currency_code = 'USD';
        $orderQueue->remarks = 'Test remarks';
        // upload is null by default

        $result = $orderQueue->getOverviewItem();

        $this->assertEquals('', $result['material']);
        $this->assertEquals(123, $result['id']);
        $this->assertEquals(0, $result['parts']);
        $this->assertEquals(0, $result['box_volume_cm3']);
        $this->assertEquals(0, $result['weight']);
        $this->assertArrayHasKey('costs', $result);
        $this->assertEquals('Test remarks', $result['remarks']);
    }

    #[Test]
    public function it_returns_default_values_without_remarks_when_is_customer_shipment_is_false_and_upload_is_null(): void
    {
        $orderQueue = new OrderQueue;
        $orderQueue->id = 456;
        $orderQueue->currency_code = 'EUR';
        $orderQueue->remarks = 'Should not appear';

        $result = $orderQueue->getOverviewItem(false);

        $this->assertEquals('', $result['material']);
        $this->assertEquals(456, $result['id']);
        $this->assertEquals(0, $result['parts']);
        $this->assertArrayNotHasKey('remarks', $result);
    }

    #[Test]
    public function it_skips_null_uploads_in_get_overview_footer(): void
    {
        // Create collection with mix of items - some with null uploads
        $orderQueueWithNullUpload = new OrderQueue;
        $orderQueueWithNullUpload->id = 1;
        $orderQueueWithNullUpload->currency_code = 'USD';
        $orderQueueWithNullUpload->manufacturer_costs = 10.00;
        // upload is null

        $items = collect([$orderQueueWithNullUpload]);

        // Should not throw exception and return valid footer
        $footer = OrderQueue::getOverviewFooter($items);

        $this->assertEquals(0, $footer['parts']);
        $this->assertEquals(0, $footer['box_volume_cm3']);
        $this->assertEquals(0, $footer['weight']);
        $this->assertArrayHasKey('costs', $footer);
    }

    #[Test]
    public function it_includes_manufacturer_costs_for_null_upload_items_in_footer_when_not_customer_shipment(): void
    {
        $orderQueueWithNullUpload = new OrderQueue;
        $orderQueueWithNullUpload->id = 1;
        $orderQueueWithNullUpload->currency_code = 'USD';
        $orderQueueWithNullUpload->setRawAttributes(['manufacturer_costs' => 5000]); // 50.00 in cents

        $items = collect([$orderQueueWithNullUpload]);

        $footer = OrderQueue::getOverviewFooter($items, false);

        $this->assertEquals(0, $footer['parts']);
        $this->assertArrayNotHasKey('remarks', $footer);
    }
}
