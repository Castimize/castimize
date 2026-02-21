<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Woocommerce;

use App\Enums\Woocommerce\WcOrderFeeTaxStatesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WcOrderFeeTaxStatesEnumTest extends TestCase
{
    #[Test]
    public function it_has_taxable_case(): void
    {
        $this->assertEquals('taxable', WcOrderFeeTaxStatesEnum::TAXABLE->value);
        $this->assertEquals('TAXABLE', WcOrderFeeTaxStatesEnum::TAXABLE->name);
    }

    #[Test]
    public function it_has_none_case(): void
    {
        $this->assertEquals('none', WcOrderFeeTaxStatesEnum::NONE->value);
        $this->assertEquals('NONE', WcOrderFeeTaxStatesEnum::NONE->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = WcOrderFeeTaxStatesEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(WcOrderFeeTaxStatesEnum::TAXABLE, $cases);
        $this->assertContains(WcOrderFeeTaxStatesEnum::NONE, $cases);
    }
}
