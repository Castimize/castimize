<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Finance;

use App\Enums\Finance\BicEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BicEnumTest extends TestCase
{
    #[Test]
    public function it_has_rabobank_case(): void
    {
        $this->assertEquals('RABONL2U', BicEnum::RABONL2U->value);
        $this->assertEquals('RABONL2U', BicEnum::RABONL2U->name);
    }

    #[Test]
    public function it_has_abn_amro_case(): void
    {
        $this->assertEquals('ABNANL2A', BicEnum::ABNANL2A->value);
    }

    #[Test]
    public function it_has_ing_case(): void
    {
        $this->assertEquals('INGBNL2A', BicEnum::INGBNL2A->value);
    }

    #[Test]
    public function it_has_knab_case(): void
    {
        $this->assertEquals('KNABNL2H', BicEnum::KNABNL2H->value);
    }

    #[Test]
    public function it_has_sns_case(): void
    {
        $this->assertEquals('SNSBNL2A', BicEnum::SNSBNL2A->value);
    }

    #[Test]
    public function it_has_triodos_case(): void
    {
        $this->assertEquals('TRIONL2U', BicEnum::TRIONL2U->value);
    }

    #[Test]
    public function it_has_regiobank_case(): void
    {
        $this->assertEquals('RBRBNL21', BicEnum::RBRBNL21->value);
    }

    #[Test]
    public function it_has_asn_case(): void
    {
        $this->assertEquals('ASNBNL21', BicEnum::ASNBNL21->value);
    }

    #[Test]
    public function it_has_bunq_case(): void
    {
        $this->assertEquals('BUNQNL2A', BicEnum::BUNQNL2A->value);
    }

    #[Test]
    public function it_has_van_lanschot_case(): void
    {
        $this->assertEquals('FVLBNL22', BicEnum::FVLBNL22->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = BicEnum::cases();

        $this->assertCount(10, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = BicEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(10, $values);
        $this->assertArrayHasKey('RABONL2U', $values);
        $this->assertArrayHasKey('ABNANL2A', $values);
        $this->assertArrayHasKey('INGBNL2A', $values);
    }

    #[Test]
    public function it_returns_bic_from_rabobank_iban(): void
    {
        $bic = BicEnum::getBicFromIban('NL91RABO0315273637');

        $this->assertEquals('RABONL2U', $bic);
    }

    #[Test]
    public function it_returns_bic_from_abn_amro_iban(): void
    {
        $bic = BicEnum::getBicFromIban('NL91ABNA0417164300');

        $this->assertEquals('ABNANL2A', $bic);
    }

    #[Test]
    public function it_returns_bic_from_ing_iban(): void
    {
        $bic = BicEnum::getBicFromIban('NL91INGB0001234567');

        $this->assertEquals('INGBNL2A', $bic);
    }

    #[Test]
    public function it_returns_bic_from_knab_iban(): void
    {
        $bic = BicEnum::getBicFromIban('NL91KNAB0001234567');

        $this->assertEquals('KNABNL2H', $bic);
    }

    #[Test]
    public function it_returns_bic_from_bunq_iban(): void
    {
        $bic = BicEnum::getBicFromIban('NL91BUNQ0001234567');

        $this->assertEquals('BUNQNL2A', $bic);
    }
}
