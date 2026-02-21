<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Woocommerce;

use App\Enums\Woocommerce\WcOrderDocumentTypesEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WcOrderDocumentTypesEnumTest extends TestCase
{
    #[Test]
    public function it_has_invoice_case(): void
    {
        $this->assertEquals('invoice', WcOrderDocumentTypesEnum::Invoice->value);
        $this->assertEquals('Invoice', WcOrderDocumentTypesEnum::Invoice->name);
    }

    #[Test]
    public function it_has_credit_note_case(): void
    {
        $this->assertEquals('credit-note', WcOrderDocumentTypesEnum::CreditNote->value);
        $this->assertEquals('CreditNote', WcOrderDocumentTypesEnum::CreditNote->name);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = WcOrderDocumentTypesEnum::cases();

        $this->assertCount(2, $cases);
        $this->assertContains(WcOrderDocumentTypesEnum::Invoice, $cases);
        $this->assertContains(WcOrderDocumentTypesEnum::CreditNote, $cases);
    }
}
