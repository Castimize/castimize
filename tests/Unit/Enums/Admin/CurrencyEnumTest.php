<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Admin;

use App\Enums\Admin\CurrencyEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CurrencyEnumTest extends TestCase
{
    #[Test]
    public function it_has_usd_case(): void
    {
        $this->assertEquals('USD', CurrencyEnum::USD->value);
        $this->assertEquals('USD', CurrencyEnum::USD->name);
    }

    #[Test]
    public function it_has_eur_case(): void
    {
        $this->assertEquals('EUR', CurrencyEnum::EUR->value);
        $this->assertEquals('EUR', CurrencyEnum::EUR->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = CurrencyEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(CurrencyEnum::USD, $cases);
        $this->assertContains(CurrencyEnum::EUR, $cases);
    }
}
