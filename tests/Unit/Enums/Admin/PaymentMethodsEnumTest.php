<?php

declare(strict_types=1);

namespace Tests\Unit\Enums\Admin;

use App\Enums\Admin\PaymentMethodsEnum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentMethodsEnumTest extends TestCase
{
    #[Test]
    public function it_has_credit_card_case(): void
    {
        $this->assertEquals('card', PaymentMethodsEnum::CREDIT_CARD->value);
    }

    #[Test]
    public function it_has_ideal_case(): void
    {
        $this->assertEquals('ideal', PaymentMethodsEnum::IDEAL->value);
    }

    #[Test]
    public function it_has_paypal_case(): void
    {
        $this->assertEquals('ppcp', PaymentMethodsEnum::PAYPAL->value);
    }

    #[Test]
    public function it_has_sepa_debit_case(): void
    {
        $this->assertEquals('sepa_debit', PaymentMethodsEnum::SEPA_DEBIT->value);
    }

    #[Test]
    public function it_has_ach_debit_case(): void
    {
        $this->assertEquals('us_bank_account', PaymentMethodsEnum::ACH_DEBIT->value);
    }

    #[Test]
    public function it_has_all_expected_cases(): void
    {
        $cases = PaymentMethodsEnum::cases();

        $this->assertGreaterThanOrEqual(30, count($cases));
    }

    #[Test]
    public function it_returns_options_array(): void
    {
        $options = PaymentMethodsEnum::options();

        $this->assertIsArray($options);
        $this->assertArrayHasKey('card', $options);
        $this->assertArrayHasKey('ideal', $options);
        $this->assertArrayHasKey('ppcp', $options);
    }

    #[Test]
    public function it_returns_mandate_options(): void
    {
        $mandateOptions = PaymentMethodsEnum::mandateOptions();

        $this->assertIsArray($mandateOptions);
        $this->assertCount(3, $mandateOptions);
        $this->assertContains('sepa_debit', $mandateOptions);
        $this->assertContains('us_bank_account', $mandateOptions);
        $this->assertContains('card', $mandateOptions);
    }

    #[Test]
    public function it_returns_woocommerce_payment_method_for_sepa(): void
    {
        $method = PaymentMethodsEnum::getWoocommercePaymentMethod('sepa');

        $this->assertEquals('stripe_sepa', $method);
    }

    #[Test]
    public function it_returns_woocommerce_payment_method_for_us_bank_account(): void
    {
        $method = PaymentMethodsEnum::getWoocommercePaymentMethod('us_bank_account');

        $this->assertEquals('stripe_ach', $method);
    }

    #[Test]
    public function it_returns_woocommerce_payment_method_default(): void
    {
        $method = PaymentMethodsEnum::getWoocommercePaymentMethod('card');

        $this->assertEquals('stripe_cc', $method);
    }

    #[Test]
    public function it_returns_woocommerce_payment_method_for_null(): void
    {
        $method = PaymentMethodsEnum::getWoocommercePaymentMethod(null);

        $this->assertEquals('stripe_cc', $method);
    }
}
