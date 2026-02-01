<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\PaymentFeeDTO;
use App\Enums\Woocommerce\WcOrderFeeTaxStatesEnum;
use App\Helpers\MonetaryAmount;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PaymentFeeDTOTest extends TestCase
{
    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $total = MonetaryAmount::fromFloat(2.50);
        $totalTax = MonetaryAmount::fromFloat(0.50);

        $dto = new PaymentFeeDTO(
            paymentMethod: 'stripe',
            name: 'Credit Card Fee',
            taxClass: 'standard',
            taxStatus: WcOrderFeeTaxStatesEnum::TAXABLE,
            total: $total,
            totalTax: $totalTax,
            taxes: ['rate' => 21],
            metaData: ['key' => 'value'],
        );

        $this->assertEquals('stripe', $dto->paymentMethod);
        $this->assertEquals('Credit Card Fee', $dto->name);
        $this->assertEquals('standard', $dto->taxClass);
        $this->assertEquals(WcOrderFeeTaxStatesEnum::TAXABLE, $dto->taxStatus);
        $this->assertEquals($total, $dto->total);
        $this->assertEquals($totalTax, $dto->totalTax);
        $this->assertEquals(['rate' => 21], $dto->taxes);
        $this->assertEquals(['key' => 'value'], $dto->metaData);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_parameters(): void
    {
        $total = MonetaryAmount::fromFloat(1.00);

        $dto = new PaymentFeeDTO(
            paymentMethod: 'paypal',
            name: 'PayPal Fee',
            taxClass: '',
            taxStatus: null,
            total: $total,
            totalTax: null,
        );

        $this->assertEquals('paypal', $dto->paymentMethod);
        $this->assertEquals('PayPal Fee', $dto->name);
        $this->assertEquals('', $dto->taxClass);
        $this->assertNull($dto->taxStatus);
        $this->assertNull($dto->totalTax);
        $this->assertEquals([], $dto->taxes);
        $this->assertEquals([], $dto->metaData);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $total = MonetaryAmount::fromFloat(3.00);
        $totalTax = MonetaryAmount::fromFloat(0.60);

        $dto = new PaymentFeeDTO(
            paymentMethod: 'ideal',
            name: 'iDEAL Fee',
            taxClass: 'standard',
            taxStatus: WcOrderFeeTaxStatesEnum::TAXABLE,
            total: $total,
            totalTax: $totalTax,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals('ideal', $array['paymentMethod']);
        $this->assertEquals('iDEAL Fee', $array['name']);
        $this->assertEquals('standard', $array['taxClass']);
        $this->assertEquals('taxable', $array['taxStatus']);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $dto = new PaymentFeeDTO(
            paymentMethod: 'bancontact',
            name: 'Bancontact Fee',
            taxClass: '',
            taxStatus: WcOrderFeeTaxStatesEnum::NONE,
            total: MonetaryAmount::fromFloat(1.50),
            totalTax: null,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals('bancontact', $decoded['paymentMethod']);
        $this->assertEquals('Bancontact Fee', $decoded['name']);
    }

    #[Test]
    public function it_handles_zero_fee(): void
    {
        $dto = new PaymentFeeDTO(
            paymentMethod: 'free',
            name: 'No Fee',
            taxClass: '',
            taxStatus: WcOrderFeeTaxStatesEnum::NONE,
            total: MonetaryAmount::fromFloat(0.00),
            totalTax: MonetaryAmount::fromFloat(0.00),
        );

        $this->assertEquals(0.00, $dto->total->toFloat());
        $this->assertEquals(0.00, $dto->totalTax->toFloat());
    }

    #[Test]
    public function it_handles_tax_status_taxable(): void
    {
        $dto = new PaymentFeeDTO(
            paymentMethod: 'card',
            name: 'Card Fee',
            taxClass: 'reduced',
            taxStatus: WcOrderFeeTaxStatesEnum::TAXABLE,
            total: MonetaryAmount::fromFloat(5.00),
            totalTax: MonetaryAmount::fromFloat(0.45),
        );

        $this->assertEquals(WcOrderFeeTaxStatesEnum::TAXABLE, $dto->taxStatus);
    }

    #[Test]
    public function it_handles_tax_status_none(): void
    {
        $dto = new PaymentFeeDTO(
            paymentMethod: 'cash',
            name: 'Cash Fee',
            taxClass: '',
            taxStatus: WcOrderFeeTaxStatesEnum::NONE,
            total: MonetaryAmount::fromFloat(0.00),
            totalTax: null,
        );

        $this->assertEquals(WcOrderFeeTaxStatesEnum::NONE, $dto->taxStatus);
    }

    #[Test]
    public function it_can_have_custom_meta_data(): void
    {
        $dto = new PaymentFeeDTO(
            paymentMethod: 'custom',
            name: 'Custom Fee',
            taxClass: '',
            taxStatus: null,
            total: MonetaryAmount::fromFloat(10.00),
            totalTax: null,
            taxes: [],
            metaData: [
                ['key' => '_custom_key', 'value' => 'custom_value'],
                ['key' => '_another_key', 'value' => 'another_value'],
            ],
        );

        $this->assertCount(2, $dto->metaData);
        $this->assertEquals('_custom_key', $dto->metaData[0]['key']);
    }
}
