<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Admin;

use App\Enums\Admin\OrderStatusesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class OrderStatusesEnumTest extends TestCase
{
    #[Test]
    public function it_has_in_queue_case(): void
    {
        $this->assertEquals('in-queue', OrderStatusesEnum::InQueue->value);
        $this->assertEquals('InQueue', OrderStatusesEnum::InQueue->name);
    }

    #[Test]
    public function it_has_in_production_case(): void
    {
        $this->assertEquals('in-production', OrderStatusesEnum::InProduction->value);
    }

    #[Test]
    public function it_has_available_for_shipping_case(): void
    {
        $this->assertEquals('available-for-shipping', OrderStatusesEnum::AvailableForShipping->value);
    }

    #[Test]
    public function it_has_in_transit_to_dc_case(): void
    {
        $this->assertEquals('in-transit-to-dc', OrderStatusesEnum::InTransitToDc->value);
    }

    #[Test]
    public function it_has_at_dc_case(): void
    {
        $this->assertEquals('at-dc', OrderStatusesEnum::AtDc->value);
    }

    #[Test]
    public function it_has_in_transit_to_customer_case(): void
    {
        $this->assertEquals('in-transit-to-customer', OrderStatusesEnum::InTransitToCustomer->value);
    }

    #[Test]
    public function it_has_rejection_request_case(): void
    {
        $this->assertEquals('rejection-request', OrderStatusesEnum::RejectionRequest->value);
    }

    #[Test]
    public function it_has_reprinted_case(): void
    {
        $this->assertEquals('reprinted', OrderStatusesEnum::Reprinted->value);
    }

    #[Test]
    public function it_has_completed_case(): void
    {
        $this->assertEquals('completed', OrderStatusesEnum::Completed->value);
    }

    #[Test]
    public function it_has_canceled_case(): void
    {
        $this->assertEquals('canceled', OrderStatusesEnum::Canceled->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = OrderStatusesEnum::cases();

        $this->assertCount(10, $cases);
    }

    #[Test]
    public function it_returns_dc_statuses(): void
    {
        $dcStatuses = OrderStatusesEnum::getDcStatuses();

        $this->assertIsArray($dcStatuses);
        $this->assertCount(4, $dcStatuses);
        $this->assertContains('at-dc', $dcStatuses);
        $this->assertContains('in-transit-to-customer', $dcStatuses);
        $this->assertContains('completed', $dcStatuses);
        $this->assertContains('canceled', $dcStatuses);
    }

    #[Test]
    public function it_returns_manufacturer_statuses(): void
    {
        $manufacturerStatuses = OrderStatusesEnum::getManufacturerStatuses();

        $this->assertIsArray($manufacturerStatuses);
        $this->assertCount(6, $manufacturerStatuses);
        $this->assertContains('in-queue', $manufacturerStatuses);
        $this->assertContains('in-production', $manufacturerStatuses);
        $this->assertContains('available-for-shipping', $manufacturerStatuses);
        $this->assertContains('in-transit-to-dc', $manufacturerStatuses);
        $this->assertContains('rejection-request', $manufacturerStatuses);
        $this->assertContains('reprinted', $manufacturerStatuses);
    }
}
