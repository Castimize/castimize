<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoLabelFileTypesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoLabelFileTypesEnumTest extends TestCase
{
    #[Test]
    public function it_has_png_case(): void
    {
        $this->assertEquals('PNG', ShippoLabelFileTypesEnum::PNG->value);
        $this->assertEquals('PNG', ShippoLabelFileTypesEnum::PNG->name);
    }

    #[Test]
    public function it_has_png_2_3x7_5_case(): void
    {
        $this->assertEquals('PNG_2.3x7.5', ShippoLabelFileTypesEnum::PNG_2_3X7_5->value);
    }

    #[Test]
    public function it_has_pdf_case(): void
    {
        $this->assertEquals('PDF', ShippoLabelFileTypesEnum::PDF->value);
    }

    #[Test]
    public function it_has_pdf_4x6_case(): void
    {
        $this->assertEquals('PDF_4x6', ShippoLabelFileTypesEnum::PDF_4X6->value);
    }

    #[Test]
    public function it_has_pdf_4x8_case(): void
    {
        $this->assertEquals('PDF_4x8', ShippoLabelFileTypesEnum::PDF_4X8->value);
    }

    #[Test]
    public function it_has_pdf_a4_case(): void
    {
        $this->assertEquals('PDF_A4', ShippoLabelFileTypesEnum::PDF_A4->value);
    }

    #[Test]
    public function it_has_pdf_a5_case(): void
    {
        $this->assertEquals('PDF_A5', ShippoLabelFileTypesEnum::PDF_A5->value);
    }

    #[Test]
    public function it_has_pdf_a6_case(): void
    {
        $this->assertEquals('PDF_A6', ShippoLabelFileTypesEnum::PDF_A6->value);
    }

    #[Test]
    public function it_has_zplii_case(): void
    {
        $this->assertEquals('ZPLII', ShippoLabelFileTypesEnum::ZPLII->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoLabelFileTypesEnum::cases();

        $this->assertCount(10, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoLabelFileTypesEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(10, $values);
        $this->assertArrayHasKey('PNG', $values);
        $this->assertArrayHasKey('PDF', $values);
        $this->assertArrayHasKey('ZPLII', $values);
    }
}
