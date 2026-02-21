<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoServicesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoServicesEnumTest extends TestCase
{
    #[Test]
    public function it_has_ups_standard_case(): void
    {
        $this->assertEquals('ups_standard', ShippoServicesEnum::UPS_STANDARD->value);
        $this->assertEquals('UPS_STANDARD', ShippoServicesEnum::UPS_STANDARD->name);
    }

    #[Test]
    public function it_has_ups_saver_case(): void
    {
        $this->assertEquals('ups_saver', ShippoServicesEnum::UPS_SAVER->value);
    }

    #[Test]
    public function it_has_ups_express_saver_worldwide_ca_case(): void
    {
        $this->assertEquals('ups_express_saver_worldwide_ca', ShippoServicesEnum::UPS_EXPRESS_SAVER_WORLDWIDE_CA->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoServicesEnum::cases();

        $this->assertCount(3, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoServicesEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(3, $values);
        $this->assertArrayHasKey('ups_standard', $values);
        $this->assertArrayHasKey('ups_saver', $values);
        $this->assertArrayHasKey('ups_express_saver_worldwide_ca', $values);
        $this->assertEquals('UPS Standard℠', $values['ups_standard']);
        $this->assertEquals('UPS Express Saver', $values['ups_saver']);
    }
}
