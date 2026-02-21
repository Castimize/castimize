<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoDistanceUnitsEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoDistanceUnitsEnumTest extends TestCase
{
    #[Test]
    public function it_has_cm_case(): void
    {
        $this->assertEquals('cm', ShippoDistanceUnitsEnum::CM->value);
        $this->assertEquals('CM', ShippoDistanceUnitsEnum::CM->name);
    }

    #[Test]
    public function it_has_in_case(): void
    {
        $this->assertEquals('in', ShippoDistanceUnitsEnum::IN->value);
    }

    #[Test]
    public function it_has_ft_case(): void
    {
        $this->assertEquals('ft', ShippoDistanceUnitsEnum::FT->value);
    }

    #[Test]
    public function it_has_mm_case(): void
    {
        $this->assertEquals('mm', ShippoDistanceUnitsEnum::MM->value);
    }

    #[Test]
    public function it_has_m_case(): void
    {
        $this->assertEquals('m', ShippoDistanceUnitsEnum::M->value);
    }

    #[Test]
    public function it_has_yd_case(): void
    {
        $this->assertEquals('yd', ShippoDistanceUnitsEnum::YD->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoDistanceUnitsEnum::cases();

        $this->assertCount(6, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoDistanceUnitsEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(6, $values);
        $this->assertArrayHasKey('cm', $values);
        $this->assertArrayHasKey('in', $values);
        $this->assertArrayHasKey('m', $values);
        $this->assertEquals('Cm', $values['cm']);
        $this->assertEquals('In', $values['in']);
    }
}
