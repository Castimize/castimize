<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoOrderStatusesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoOrderStatusesEnumTest extends TestCase
{
    #[Test]
    public function it_has_unknown_case(): void
    {
        $this->assertEquals('UNKNOWN', ShippoOrderStatusesEnum::UNKNOWN->value);
    }

    #[Test]
    public function it_has_await_pay_case(): void
    {
        $this->assertEquals('AWAITPAY', ShippoOrderStatusesEnum::AWAIT_PAY->value);
        $this->assertEquals('AWAIT_PAY', ShippoOrderStatusesEnum::AWAIT_PAY->name);
    }

    #[Test]
    public function it_has_paid_case(): void
    {
        $this->assertEquals('PAID', ShippoOrderStatusesEnum::PAID->value);
    }

    #[Test]
    public function it_has_refunded_case(): void
    {
        $this->assertEquals('REFUNDED', ShippoOrderStatusesEnum::REFUNDED->value);
    }

    #[Test]
    public function it_has_cancelled_case(): void
    {
        $this->assertEquals('CANCELLED', ShippoOrderStatusesEnum::CANCELLED->value);
    }

    #[Test]
    public function it_has_partially_fulfilled_case(): void
    {
        $this->assertEquals('PARTIALLY_FULFILLED', ShippoOrderStatusesEnum::PARTIALLY_FULFILLED->value);
    }

    #[Test]
    public function it_has_shipped_case(): void
    {
        $this->assertEquals('SHIPPED', ShippoOrderStatusesEnum::SHIPPED->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoOrderStatusesEnum::cases();

        $this->assertCount(7, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoOrderStatusesEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(7, $values);
        $this->assertArrayHasKey('UNKNOWN', $values);
        $this->assertArrayHasKey('PAID', $values);
        $this->assertArrayHasKey('SHIPPED', $values);
    }
}
