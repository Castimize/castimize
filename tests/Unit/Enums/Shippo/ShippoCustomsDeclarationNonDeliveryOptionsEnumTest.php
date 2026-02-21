<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoCustomsDeclarationNonDeliveryOptionsEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoCustomsDeclarationNonDeliveryOptionsEnumTest extends TestCase
{
    #[Test]
    public function it_has_abandon_case(): void
    {
        $this->assertEquals('ABANDON', ShippoCustomsDeclarationNonDeliveryOptionsEnum::ABANDON->value);
        $this->assertEquals('ABANDON', ShippoCustomsDeclarationNonDeliveryOptionsEnum::ABANDON->name);
    }

    #[Test]
    public function it_has_return_case(): void
    {
        $this->assertEquals('RETURN', ShippoCustomsDeclarationNonDeliveryOptionsEnum::RETURN->value);
        $this->assertEquals('RETURN', ShippoCustomsDeclarationNonDeliveryOptionsEnum::RETURN->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoCustomsDeclarationNonDeliveryOptionsEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(ShippoCustomsDeclarationNonDeliveryOptionsEnum::ABANDON, $cases);
        $this->assertContains(ShippoCustomsDeclarationNonDeliveryOptionsEnum::RETURN, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoCustomsDeclarationNonDeliveryOptionsEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(2, $values);
        $this->assertArrayHasKey('ABANDON', $values);
        $this->assertArrayHasKey('RETURN', $values);
    }
}
