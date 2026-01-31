<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Currency;
use App\Models\Customer;
use App\Models\CustomerShipment;
use App\Models\OrderQueue;
use App\Models\TrackingStatus;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CustomerShipmentTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $shipment = new CustomerShipment;
        $fillable = $shipment->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('currency_id', $fillable);
        $this->assertContains('eta', $fillable);
        $this->assertContains('sent_at', $fillable);
        $this->assertContains('arrived_at', $fillable);
        $this->assertContains('total_parts', $fillable);
        $this->assertContains('total_costs', $fillable);
        $this->assertContains('service_lead_time', $fillable);
        $this->assertContains('tracking_number', $fillable);
    }

    #[Test]
    public function it_casts_dates_correctly(): void
    {
        $shipment = new CustomerShipment;
        $casts = $shipment->getCasts();

        $this->assertEquals('datetime', $casts['created_at']);
        $this->assertEquals('datetime', $casts['updated_at']);
        $this->assertEquals('datetime', $casts['deleted_at']);
        $this->assertEquals('datetime', $casts['sent_at']);
        $this->assertEquals('datetime', $casts['arrived_at']);
        $this->assertEquals('datetime', $casts['expected_delivery_date']);
        $this->assertEquals('datetime', $casts['service_lead_time']);
    }

    #[Test]
    public function it_casts_json_fields_correctly(): void
    {
        $shipment = new CustomerShipment;
        $casts = $shipment->getCasts();

        $this->assertEquals('json', $casts['shippo_shipment_meta_data']);
        $this->assertEquals('json', $casts['shippo_transaction_meta_data']);
    }

    #[Test]
    public function it_casts_selected_pos_as_array_object(): void
    {
        $shipment = new CustomerShipment;
        $casts = $shipment->getCasts();

        $this->assertEquals(AsArrayObject::class, $casts['selected_pos']);
    }

    #[Test]
    public function it_converts_total_costs_from_cents(): void
    {
        $shipment = new CustomerShipment;
        $shipment->setRawAttributes(['total_costs' => 5000]);

        $this->assertEquals(50.00, $shipment->total_costs);
    }

    #[Test]
    public function it_converts_service_costs_from_cents(): void
    {
        $shipment = new CustomerShipment;
        $shipment->setRawAttributes(['service_costs' => 1000]);

        $this->assertEquals(10.00, $shipment->service_costs);
    }

    #[Test]
    public function it_belongs_to_customer(): void
    {
        $shipment = new CustomerShipment;

        $this->assertInstanceOf(BelongsTo::class, $shipment->customer());
        $this->assertEquals(Customer::class, $shipment->customer()->getRelated()::class);
    }

    #[Test]
    public function it_belongs_to_currency(): void
    {
        $shipment = new CustomerShipment;

        $this->assertInstanceOf(BelongsTo::class, $shipment->currency());
        $this->assertEquals(Currency::class, $shipment->currency()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_order_queues(): void
    {
        $shipment = new CustomerShipment;

        $this->assertInstanceOf(HasMany::class, $shipment->orderQueues());
        $this->assertEquals(OrderQueue::class, $shipment->orderQueues()->getRelated()::class);
    }

    #[Test]
    public function it_has_many_tracking_statuses(): void
    {
        $shipment = new CustomerShipment;

        $this->assertInstanceOf(MorphMany::class, $shipment->trackingStatuses());
        $this->assertEquals(TrackingStatus::class, $shipment->trackingStatuses()->getRelated()::class);
    }
}
