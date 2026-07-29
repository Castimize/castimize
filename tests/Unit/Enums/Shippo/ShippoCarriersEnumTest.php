<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoCarriersEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoCarriersEnumTest extends TestCase
{
    #[Test]
    public function it_has_ups_case(): void
    {
        $this->assertEquals('ups', ShippoCarriersEnum::UPS->value);
        $this->assertEquals('UPS', ShippoCarriersEnum::UPS->name);
    }

    #[Test]
    public function it_has_fedex_case(): void
    {
        $this->assertEquals('fedex', ShippoCarriersEnum::FedEx->value);
        $this->assertEquals('FedEx', ShippoCarriersEnum::FedEx->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoCarriersEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(ShippoCarriersEnum::UPS, $cases);
        $this->assertContains(ShippoCarriersEnum::FedEx, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoCarriersEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(2, $values);
        $this->assertArrayHasKey('ups', $values);
        $this->assertEquals('UPS', $values['ups']);
        $this->assertArrayHasKey('fedex', $values);
        $this->assertEquals('FedEx', $values['fedex']);
    }
}
