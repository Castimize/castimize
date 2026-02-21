<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoBuildingTypesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoBuildingTypesEnumTest extends TestCase
{
    #[Test]
    public function it_has_apartment_case(): void
    {
        $this->assertEquals('apartment', ShippoBuildingTypesEnum::Apartment->value);
        $this->assertEquals('Apartment', ShippoBuildingTypesEnum::Apartment->name);
    }

    #[Test]
    public function it_has_building_case(): void
    {
        $this->assertEquals('building', ShippoBuildingTypesEnum::Building->value);
    }

    #[Test]
    public function it_has_department_case(): void
    {
        $this->assertEquals('department', ShippoBuildingTypesEnum::Department->value);
    }

    #[Test]
    public function it_has_floor_case(): void
    {
        $this->assertEquals('floor', ShippoBuildingTypesEnum::Floor->value);
    }

    #[Test]
    public function it_has_room_case(): void
    {
        $this->assertEquals('room', ShippoBuildingTypesEnum::Room->value);
    }

    #[Test]
    public function it_has_suite_case(): void
    {
        $this->assertEquals('suite', ShippoBuildingTypesEnum::Suite->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoBuildingTypesEnum::cases();

        $this->assertCount(6, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoBuildingTypesEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(6, $values);
        $this->assertArrayHasKey('apartment', $values);
        $this->assertArrayHasKey('building', $values);
        $this->assertArrayHasKey('suite', $values);
    }
}
