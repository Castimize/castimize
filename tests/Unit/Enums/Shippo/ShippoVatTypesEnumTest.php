<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoVatTypesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoVatTypesEnumTest extends TestCase
{
    #[Test]
    public function it_has_ein_case(): void
    {
        $this->assertEquals('EIN', ShippoVatTypesEnum::EIN->value);
        $this->assertEquals('EIN', ShippoVatTypesEnum::EIN->name);
    }

    #[Test]
    public function it_has_vat_case(): void
    {
        $this->assertEquals('VAT', ShippoVatTypesEnum::VAT->value);
    }

    #[Test]
    public function it_has_ioss_case(): void
    {
        $this->assertEquals('IOSS', ShippoVatTypesEnum::IOSS->value);
    }

    #[Test]
    public function it_has_arn_case(): void
    {
        $this->assertEquals('ARN', ShippoVatTypesEnum::ARN->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoVatTypesEnum::cases();

        $this->assertCount(4, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoVatTypesEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(4, $values);
        $this->assertArrayHasKey('EIN', $values);
        $this->assertArrayHasKey('VAT', $values);
        $this->assertArrayHasKey('IOSS', $values);
        $this->assertArrayHasKey('ARN', $values);
    }
}
