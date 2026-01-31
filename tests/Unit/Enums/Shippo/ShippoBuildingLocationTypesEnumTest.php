<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoBuildingLocationTypesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoBuildingLocationTypesEnumTest extends TestCase
{
    #[Test]
    public function it_has_front_door_case(): void
    {
        $this->assertEquals('Front Door', ShippoBuildingLocationTypesEnum::FrontDoor->value);
        $this->assertEquals('FrontDoor', ShippoBuildingLocationTypesEnum::FrontDoor->name);
    }

    #[Test]
    public function it_has_back_door_case(): void
    {
        $this->assertEquals('Back Door', ShippoBuildingLocationTypesEnum::BackDoor->value);
    }

    #[Test]
    public function it_has_side_door_case(): void
    {
        $this->assertEquals('Side Door', ShippoBuildingLocationTypesEnum::SideDoor->value);
    }

    #[Test]
    public function it_has_knock_on_door_case(): void
    {
        $this->assertEquals('Knock on Door', ShippoBuildingLocationTypesEnum::KnockOnDoor->value);
    }

    #[Test]
    public function it_has_ring_bell_case(): void
    {
        $this->assertEquals('Ring Bell', ShippoBuildingLocationTypesEnum::RingBell->value);
    }

    #[Test]
    public function it_has_mail_room_case(): void
    {
        $this->assertEquals('Mail Room', ShippoBuildingLocationTypesEnum::MailRoom->value);
    }

    #[Test]
    public function it_has_office_case(): void
    {
        $this->assertEquals('Office', ShippoBuildingLocationTypesEnum::Office->value);
    }

    #[Test]
    public function it_has_reception_case(): void
    {
        $this->assertEquals('Reception', ShippoBuildingLocationTypesEnum::Reception->value);
    }

    #[Test]
    public function it_has_other_case(): void
    {
        $this->assertEquals('Other', ShippoBuildingLocationTypesEnum::Other->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoBuildingLocationTypesEnum::cases();

        $this->assertCount(12, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoBuildingLocationTypesEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(12, $values);
        $this->assertArrayHasKey('Front Door', $values);
        $this->assertArrayHasKey('Back Door', $values);
        $this->assertArrayHasKey('Other', $values);
    }
}
