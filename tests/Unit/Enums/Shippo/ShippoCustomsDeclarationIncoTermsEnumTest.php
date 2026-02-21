<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoCustomsDeclarationIncoTermsEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoCustomsDeclarationIncoTermsEnumTest extends TestCase
{
    #[Test]
    public function it_has_ddp_case(): void
    {
        $this->assertEquals('DDP', ShippoCustomsDeclarationIncoTermsEnum::DDP->value);
        $this->assertEquals('DDP', ShippoCustomsDeclarationIncoTermsEnum::DDP->name);
    }

    #[Test]
    public function it_has_ddu_case(): void
    {
        $this->assertEquals('DDU', ShippoCustomsDeclarationIncoTermsEnum::DDU->value);
    }

    #[Test]
    public function it_has_fca_case(): void
    {
        $this->assertEquals('FCA', ShippoCustomsDeclarationIncoTermsEnum::FCA->value);
    }

    #[Test]
    public function it_has_dap_case(): void
    {
        $this->assertEquals('DAP', ShippoCustomsDeclarationIncoTermsEnum::DAP->value);
    }

    #[Test]
    public function it_has_edap_case(): void
    {
        $this->assertEquals('eDAP', ShippoCustomsDeclarationIncoTermsEnum::EDAP->value);
        $this->assertEquals('EDAP', ShippoCustomsDeclarationIncoTermsEnum::EDAP->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoCustomsDeclarationIncoTermsEnum::cases();

        $this->assertCount(5, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoCustomsDeclarationIncoTermsEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(5, $values);
        $this->assertArrayHasKey('DDP', $values);
        $this->assertArrayHasKey('DDU', $values);
        $this->assertArrayHasKey('eDAP', $values);
    }
}
