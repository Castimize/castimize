<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Admin;

use App\Enums\Admin\ShippingServiceLevelTokenEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippingServiceLevelTokenEnumTest extends TestCase
{
    #[Test]
    public function it_has_ups_standard_case(): void
    {
        $this->assertEquals('ups_standard', ShippingServiceLevelTokenEnum::UpsStandard->value);
        $this->assertEquals('UpsStandard', ShippingServiceLevelTokenEnum::UpsStandard->name);
    }

    #[Test]
    public function it_has_ups_saver_case(): void
    {
        $this->assertEquals('ups_saver', ShippingServiceLevelTokenEnum::UpsSaver->value);
        $this->assertEquals('UpsSaver', ShippingServiceLevelTokenEnum::UpsSaver->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippingServiceLevelTokenEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(ShippingServiceLevelTokenEnum::UpsStandard, $cases);
        $this->assertContains(ShippingServiceLevelTokenEnum::UpsSaver, $cases);
    }
}
