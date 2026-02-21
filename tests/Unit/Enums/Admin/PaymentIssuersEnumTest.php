<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Admin;

use App\Enums\Admin\PaymentIssuersEnum;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentIssuersEnumTest extends TestCase
{
    #[Test]
    public function it_has_stripe_old_case(): void
    {
        $this->assertEquals('stripe', PaymentIssuersEnum::StripeOld->value);
    }

    #[Test]
    public function it_has_stripe_bancontact_case(): void
    {
        $this->assertEquals('stripe_bancontact', PaymentIssuersEnum::StripeBancontact->value);
    }

    #[Test]
    public function it_has_stripe_credit_card_case(): void
    {
        $this->assertEquals('stripe_cc', PaymentIssuersEnum::StripeCreditCard->value);
    }

    #[Test]
    public function it_has_stripe_ideal_case(): void
    {
        $this->assertEquals('stripe_ideal', PaymentIssuersEnum::StripeIdeal->value);
    }

    #[Test]
    public function it_has_stripe_sepa_case(): void
    {
        $this->assertEquals('stripe_sepa', PaymentIssuersEnum::StripeSepa->value);
    }

    #[Test]
    public function it_has_stripe_sofort_case(): void
    {
        $this->assertEquals('stripe_sofort', PaymentIssuersEnum::StripeSofort->value);
    }

    #[Test]
    public function it_has_paypal_case(): void
    {
        $this->assertEquals('ppcp', PaymentIssuersEnum::Paypal->value);
    }

    #[Test]
    public function it_has_direct_bank_transfer_case(): void
    {
        $this->assertEquals('bacs', PaymentIssuersEnum::DirectBankTransfer->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = PaymentIssuersEnum::cases();

        $this->assertCount(8, $cases);
    }

    #[Test]
    public function it_returns_stripe_methods(): void
    {
        $stripeMethods = PaymentIssuersEnum::getStripeMethods();

        $this->assertIsArray($stripeMethods);
        $this->assertCount(6, $stripeMethods);
        $this->assertContains('stripe', $stripeMethods);
        $this->assertContains('stripe_bancontact', $stripeMethods);
        $this->assertContains('stripe_cc', $stripeMethods);
        $this->assertContains('stripe_ideal', $stripeMethods);
        $this->assertContains('stripe_sepa', $stripeMethods);
        $this->assertContains('stripe_sofort', $stripeMethods);
        $this->assertNotContains('ppcp', $stripeMethods);
        $this->assertNotContains('bacs', $stripeMethods);
    }
}
