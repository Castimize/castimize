<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Shippo;

use App\Enums\Shippo\ShippoCustomsDeclarationContentTypesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ShippoCustomsDeclarationContentTypesEnumTest extends TestCase
{
    #[Test]
    public function it_has_documents_case(): void
    {
        $this->assertEquals('DOCUMENTS', ShippoCustomsDeclarationContentTypesEnum::DOCUMENTS->value);
    }

    #[Test]
    public function it_has_gift_case(): void
    {
        $this->assertEquals('GIFT', ShippoCustomsDeclarationContentTypesEnum::GIFT->value);
    }

    #[Test]
    public function it_has_sample_case(): void
    {
        $this->assertEquals('SAMPLE', ShippoCustomsDeclarationContentTypesEnum::SAMPLE->value);
    }

    #[Test]
    public function it_has_merchandise_case(): void
    {
        $this->assertEquals('MERCHANDISE', ShippoCustomsDeclarationContentTypesEnum::MERCHANDISE->value);
    }

    #[Test]
    public function it_has_humanitarian_donation_case(): void
    {
        $this->assertEquals('HUMANITARIAN_DONATION', ShippoCustomsDeclarationContentTypesEnum::HUMANITARIAN_DONATION->value);
    }

    #[Test]
    public function it_has_return_merchandise_case(): void
    {
        $this->assertEquals('RETURN_MERCHANDISE', ShippoCustomsDeclarationContentTypesEnum::RETURN_MERCHANDISE->value);
    }

    #[Test]
    public function it_has_other_case(): void
    {
        $this->assertEquals('OTHER', ShippoCustomsDeclarationContentTypesEnum::OTHER->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = ShippoCustomsDeclarationContentTypesEnum::cases();

        $this->assertCount(7, $cases);
    }

    #[Test]
    public function it_returns_values_array(): void
    {
        $values = ShippoCustomsDeclarationContentTypesEnum::values();

        $this->assertIsArray($values);
        $this->assertCount(7, $values);
        $this->assertArrayHasKey('DOCUMENTS', $values);
        $this->assertArrayHasKey('MERCHANDISE', $values);
    }
}
