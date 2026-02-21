<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Admin;

use App\Enums\Admin\PaymentFeeTypesEnum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentFeeTypesEnumTest extends TestCase
{
    #[Test]
    public function it_has_fixed_case(): void
    {
        $this->assertEquals('fixed', PaymentFeeTypesEnum::FIXED->value);
        $this->assertEquals('FIXED', PaymentFeeTypesEnum::FIXED->name);
    }

    #[Test]
    public function it_has_percentage_case(): void
    {
        $this->assertEquals('percentage', PaymentFeeTypesEnum::PERCENTAGE->value);
        $this->assertEquals('PERCENTAGE', PaymentFeeTypesEnum::PERCENTAGE->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = PaymentFeeTypesEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(PaymentFeeTypesEnum::FIXED, $cases);
        $this->assertContains(PaymentFeeTypesEnum::PERCENTAGE, $cases);
    }

    #[Test]
    public function it_returns_options_array(): void
    {
        $options = PaymentFeeTypesEnum::options();

        $this->assertIsArray($options);
        $this->assertCount(2, $options);
        $this->assertArrayHasKey('fixed', $options);
        $this->assertArrayHasKey('percentage', $options);
    }
}
