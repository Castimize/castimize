<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\Admin\PaymentFeeTypesEnum;
use App\Models\PaymentFee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentFeeTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_has_fillable_attributes(): void
    {
        $paymentFee = new PaymentFee;
        $fillable = $paymentFee->getFillable();

        $this->assertContains('currency_id', $fillable);
        $this->assertContains('payment_method', $fillable);
        $this->assertContains('type', $fillable);
        $this->assertContains('fee', $fillable);
        $this->assertContains('minimum_fee', $fillable);
        $this->assertContains('maximum_fee', $fillable);
        $this->assertContains('currency_code', $fillable);
    }

    #[Test]
    public function it_converts_fee_from_cents_when_fixed_type(): void
    {
        $paymentFee = new PaymentFee;
        $paymentFee->setRawAttributes([
            'type' => PaymentFeeTypesEnum::FIXED->value,
            'fee' => 100,
        ]);

        $this->assertEquals(1.00, $paymentFee->fee);
    }

    #[Test]
    public function it_converts_fee_to_percentage_when_percentage_type(): void
    {
        // When type is PERCENTAGE, the accessor multiplies by 100 to convert from decimal to percentage
        // e.g., 0.25 (25%) stored in DB becomes 25.0 when retrieved
        $paymentFee = new PaymentFee;
        $paymentFee->setRawAttributes([
            'type' => PaymentFeeTypesEnum::PERCENTAGE->value,
            'fee' => 0.25,
        ]);

        $this->assertEquals(25.0, $paymentFee->fee);
    }

    #[Test]
    public function it_converts_minimum_fee_from_cents_when_fixed_type(): void
    {
        $paymentFee = new PaymentFee;
        $paymentFee->setRawAttributes([
            'type' => PaymentFeeTypesEnum::FIXED->value,
            'minimum_fee' => 50,
        ]);

        $this->assertEquals(0.50, $paymentFee->minimum_fee);
    }

    #[Test]
    public function it_converts_maximum_fee_from_cents_when_fixed_type(): void
    {
        $paymentFee = new PaymentFee;
        $paymentFee->setRawAttributes([
            'type' => PaymentFeeTypesEnum::FIXED->value,
            'maximum_fee' => 500,
        ]);

        $this->assertEquals(5.00, $paymentFee->maximum_fee);
    }
}
