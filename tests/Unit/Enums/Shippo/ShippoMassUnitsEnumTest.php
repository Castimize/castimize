<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoMassUnitsEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoMassUnitsEnumTest extends TestCase
{
    #[Test]
    public function it_has_g_case(): void
    {
        $this->assertEquals('g', ShippoMassUnitsEnum::G->value);
        $this->assertEquals('G', ShippoMassUnitsEnum::G->name);
    }

    #[Test]
    public function it_has_oz_case(): void
    {
        $this->assertEquals('oz', ShippoMassUnitsEnum::OZ->value);
    }

    #[Test]
    public function it_has_lb_case(): void
    {
        $this->assertEquals('lb', ShippoMassUnitsEnum::LB->value);
    }

    #[Test]
    public function it_has_kg_case(): void
    {
        $this->assertEquals('kg', ShippoMassUnitsEnum::KG->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoMassUnitsEnum::cases();

        $this->assertCount(4, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoMassUnitsEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(4, $values);
        $this->assertArrayHasKey('g', $values);
        $this->assertArrayHasKey('oz', $values);
        $this->assertArrayHasKey('lb', $values);
        $this->assertArrayHasKey('kg', $values);
        $this->assertEquals('G', $values['g']);
        $this->assertEquals('Kg', $values['kg']);
    }
}
