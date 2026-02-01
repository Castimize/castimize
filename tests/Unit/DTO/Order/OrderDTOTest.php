<?php

declare(strict_types=1);

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\OrderDTO;
use App\DTO\Order\PaymentFeeDTO;
use App\DTO\Order\UploadDTO;
use App\Enums\Woocommerce\WcOrderFeeTaxStatesEnum;
use App\Helpers\MonetaryAmount;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderDTOTest extends TestCase
{
    private function createUploadDTO(): UploadDTO
    {
        return new UploadDTO(
            wpId: '12345',
            materialId: 1,
            materialName: 'PLA White',
            name: 'Test Model',
            fileName: 'test.stl',
            modelVolumeCc: 15.5,
            modelXLength: 10.0,
            modelYLength: 20.0,
            modelZLength: 5.0,
            modelBoxVolume: 1000.0,
            surfaceArea: 500.0,
            modelParts: 1,
            quantity: 1,
            inCents: false,
            subtotal: MonetaryAmount::fromFloat(50.00),
            subtotalTax: MonetaryAmount::fromFloat(10.50),
            total: MonetaryAmount::fromFloat(50.00),
            totalTax: MonetaryAmount::fromFloat(10.50),
            metaData: null,
            customerLeadTime: 5,
        );
    }

    private function createPaymentFeeDTO(): PaymentFeeDTO
    {
        return new PaymentFeeDTO(
            paymentMethod: 'stripe',
            name: 'Credit Card Fee',
            taxClass: '',
            taxStatus: WcOrderFeeTaxStatesEnum::TAXABLE,
            total: MonetaryAmount::fromFloat(2.50),
            totalTax: MonetaryAmount::fromFloat(0.53),
        );
    }

    #[Test]
    public function it_can_be_instantiated_with_all_parameters(): void
    {
        $uploads = collect([$this->createUploadDTO()]);
        $paymentFees = collect([$this->createPaymentFeeDTO()]);
        $createdAt = Carbon::now();
        $updatedAt = Carbon::now();
        $paidAt = Carbon::now();

        $dto = new OrderDTO(
            customerId: 1,
            customerStripeId: 'cus_123456',
            shopReceiptId: 789,
            source: 'wp',
            wpId: 100,
            orderNumber: 1001,
            orderKey: 'wc_order_abc123',
            status: 'processing',
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            billingFirstName: 'John',
            billingLastName: 'Doe',
            billingCompany: 'Acme Inc',
            billingPhoneNumber: '+31612345678',
            billingEmail: 'john@example.com',
            billingAddressLine1: '123 Main St',
            billingAddressLine2: 'Apt 4',
            billingPostalCode: '1011 AB',
            billingCity: 'Amsterdam',
            billingState: 'NH',
            billingCountry: 'NL',
            billingVatNumber: 'NL123456789B01',
            shippingFirstName: 'John',
            shippingLastName: 'Doe',
            shippingCompany: null,
            shippingPhoneNumber: '+31612345678',
            shippingEmail: 'john@example.com',
            shippingAddressLine1: '123 Main St',
            shippingAddressLine2: 'Apt 4',
            shippingPostalCode: '1011 AB',
            shippingCity: 'Amsterdam',
            shippingState: 'NH',
            shippingCountry: 'NL',
            inCents: false,
            shippingFee: MonetaryAmount::fromFloat(9.99),
            shippingFeeTax: MonetaryAmount::fromFloat(2.10),
            discountFee: MonetaryAmount::fromFloat(5.00),
            discountFeeTax: MonetaryAmount::fromFloat(1.05),
            total: MonetaryAmount::fromFloat(57.54),
            totalTax: MonetaryAmount::fromFloat(12.08),
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: 21.0,
            currencyCode: 'EUR',
            paymentMethod: 'Credit Card',
            paymentIssuer: 'stripe',
            transactionId: 'txn_123',
            paymentIntentId: 'pi_123',
            customerIpAddress: '192.168.1.1',
            customerUserAgent: 'Mozilla/5.0',
            metaData: ['key' => 'value'],
            comments: 'Please handle with care',
            promoCode: 'DISCOUNT10',
            isPaid: true,
            paidAt: $paidAt,
            createdAt: $createdAt,
            updatedAt: $updatedAt,
            uploads: $uploads,
            paymentFees: $paymentFees,
        );

        $this->assertEquals(1, $dto->customerId);
        $this->assertEquals('cus_123456', $dto->customerStripeId);
        $this->assertEquals(789, $dto->shopReceiptId);
        $this->assertEquals('wp', $dto->source);
        $this->assertEquals(100, $dto->wpId);
        $this->assertEquals(1001, $dto->orderNumber);
        $this->assertEquals('wc_order_abc123', $dto->orderKey);
        $this->assertEquals('processing', $dto->status);
        $this->assertEquals('John', $dto->firstName);
        $this->assertEquals('Doe', $dto->lastName);
        $this->assertEquals('john@example.com', $dto->email);
        $this->assertEquals('John', $dto->billingFirstName);
        $this->assertEquals('Acme Inc', $dto->billingCompany);
        $this->assertEquals('NL123456789B01', $dto->billingVatNumber);
        $this->assertEquals('John', $dto->shippingFirstName);
        $this->assertEquals('NL', $dto->shippingCountry);
        $this->assertFalse($dto->inCents);
        $this->assertEquals(9.99, $dto->shippingFee->toFloat());
        $this->assertEquals(5.00, $dto->discountFee->toFloat());
        $this->assertEquals(57.54, $dto->total->toFloat());
        $this->assertEquals(21.0, $dto->taxPercentage);
        $this->assertEquals('EUR', $dto->currencyCode);
        $this->assertEquals('Credit Card', $dto->paymentMethod);
        $this->assertEquals('stripe', $dto->paymentIssuer);
        $this->assertEquals('txn_123', $dto->transactionId);
        $this->assertEquals('pi_123', $dto->paymentIntentId);
        $this->assertEquals('DISCOUNT10', $dto->promoCode);
        $this->assertTrue($dto->isPaid);
        $this->assertEquals($paidAt, $dto->paidAt);
        $this->assertCount(1, $dto->uploads);
        $this->assertCount(1, $dto->paymentFees);
    }

    #[Test]
    public function it_can_be_instantiated_with_nullable_parameters(): void
    {
        $uploads = collect([$this->createUploadDTO()]);

        $dto = new OrderDTO(
            customerId: 1,
            customerStripeId: null,
            shopReceiptId: null,
            source: 'wp',
            wpId: null,
            orderNumber: 1002,
            orderKey: null,
            status: 'pending',
            firstName: null,
            lastName: null,
            email: 'test@example.com',
            billingFirstName: 'Test',
            billingLastName: 'User',
            billingCompany: null,
            billingPhoneNumber: '+31600000000',
            billingEmail: 'test@example.com',
            billingAddressLine1: '456 Test St',
            billingAddressLine2: null,
            billingPostalCode: '2000 AA',
            billingCity: 'Rotterdam',
            billingState: null,
            billingCountry: 'NL',
            billingVatNumber: null,
            shippingFirstName: 'Test',
            shippingLastName: 'User',
            shippingCompany: null,
            shippingPhoneNumber: null,
            shippingEmail: null,
            shippingAddressLine1: '456 Test St',
            shippingAddressLine2: null,
            shippingPostalCode: '2000 AA',
            shippingCity: 'Rotterdam',
            shippingState: null,
            shippingCountry: 'NL',
            inCents: false,
            shippingFee: null,
            shippingFeeTax: null,
            discountFee: null,
            discountFeeTax: null,
            total: null,
            totalTax: null,
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: null,
            currencyCode: 'EUR',
            paymentMethod: 'Bank Transfer',
            paymentIssuer: 'bacs',
            transactionId: null,
            paymentIntentId: null,
            customerIpAddress: null,
            customerUserAgent: null,
            metaData: null,
            comments: null,
            promoCode: null,
            isPaid: false,
            paidAt: null,
            createdAt: null,
            updatedAt: null,
            uploads: $uploads,
            paymentFees: null,
        );

        $this->assertEquals(1, $dto->customerId);
        $this->assertNull($dto->customerStripeId);
        $this->assertNull($dto->shopReceiptId);
        $this->assertNull($dto->wpId);
        $this->assertNull($dto->billingCompany);
        $this->assertNull($dto->billingVatNumber);
        $this->assertNull($dto->shippingFee);
        $this->assertNull($dto->discountFee);
        $this->assertNull($dto->total);
        $this->assertNull($dto->taxPercentage);
        $this->assertFalse($dto->isPaid);
        $this->assertNull($dto->paidAt);
        $this->assertNull($dto->paymentFees);
    }

    #[Test]
    public function it_can_be_converted_to_array(): void
    {
        $uploads = collect([$this->createUploadDTO()]);
        $paymentFees = collect([$this->createPaymentFeeDTO()]);

        $dto = new OrderDTO(
            customerId: 5,
            customerStripeId: 'cus_abc',
            shopReceiptId: null,
            source: 'wp',
            wpId: 500,
            orderNumber: 5000,
            orderKey: 'wc_order_xyz',
            status: 'completed',
            firstName: 'Jane',
            lastName: 'Smith',
            email: 'jane@example.com',
            billingFirstName: 'Jane',
            billingLastName: 'Smith',
            billingCompany: null,
            billingPhoneNumber: '+31699999999',
            billingEmail: 'jane@example.com',
            billingAddressLine1: '789 Oak Ave',
            billingAddressLine2: null,
            billingPostalCode: '3000 BB',
            billingCity: 'Utrecht',
            billingState: null,
            billingCountry: 'NL',
            billingVatNumber: null,
            shippingFirstName: 'Jane',
            shippingLastName: 'Smith',
            shippingCompany: null,
            shippingPhoneNumber: '+31699999999',
            shippingEmail: 'jane@example.com',
            shippingAddressLine1: '789 Oak Ave',
            shippingAddressLine2: null,
            shippingPostalCode: '3000 BB',
            shippingCity: 'Utrecht',
            shippingState: null,
            shippingCountry: 'NL',
            inCents: false,
            shippingFee: MonetaryAmount::fromFloat(5.00),
            shippingFeeTax: MonetaryAmount::fromFloat(1.05),
            discountFee: null,
            discountFeeTax: null,
            total: MonetaryAmount::fromFloat(55.00),
            totalTax: MonetaryAmount::fromFloat(11.55),
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: 21.0,
            currencyCode: 'EUR',
            paymentMethod: 'iDEAL',
            paymentIssuer: 'ideal',
            transactionId: null,
            paymentIntentId: 'pi_xyz',
            customerIpAddress: '10.0.0.1',
            customerUserAgent: 'Chrome',
            metaData: null,
            comments: null,
            promoCode: null,
            isPaid: true,
            paidAt: Carbon::now(),
            createdAt: Carbon::now(),
            updatedAt: Carbon::now(),
            uploads: $uploads,
            paymentFees: $paymentFees,
        );

        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertEquals(5, $array['customerId']);
        $this->assertEquals('cus_abc', $array['customerStripeId']);
        $this->assertEquals('wp', $array['source']);
        $this->assertEquals(500, $array['wpId']);
        $this->assertEquals(5000, $array['orderNumber']);
        $this->assertEquals('completed', $array['status']);
        $this->assertEquals('Jane', $array['firstName']);
        $this->assertEquals('jane@example.com', $array['email']);
        $this->assertEquals('EUR', $array['currencyCode']);
        $this->assertTrue($array['isPaid']);
        $this->assertIsArray($array['uploads']);
        $this->assertIsArray($array['paymentFees']);
    }

    #[Test]
    public function it_can_be_converted_to_json(): void
    {
        $uploads = collect([$this->createUploadDTO()]);

        $dto = new OrderDTO(
            customerId: 10,
            customerStripeId: null,
            shopReceiptId: null,
            source: 'etsy',
            wpId: null,
            orderNumber: 10000,
            orderKey: null,
            status: 'processing',
            firstName: 'Bob',
            lastName: 'Johnson',
            email: 'bob@example.com',
            billingFirstName: 'Bob',
            billingLastName: 'Johnson',
            billingCompany: null,
            billingPhoneNumber: '+1234567890',
            billingEmail: 'bob@example.com',
            billingAddressLine1: '100 Main St',
            billingAddressLine2: null,
            billingPostalCode: '10001',
            billingCity: 'New York',
            billingState: 'NY',
            billingCountry: 'US',
            billingVatNumber: null,
            shippingFirstName: 'Bob',
            shippingLastName: 'Johnson',
            shippingCompany: null,
            shippingPhoneNumber: '+1234567890',
            shippingEmail: 'bob@example.com',
            shippingAddressLine1: '100 Main St',
            shippingAddressLine2: null,
            shippingPostalCode: '10001',
            shippingCity: 'New York',
            shippingState: 'NY',
            shippingCountry: 'US',
            inCents: true,
            shippingFee: MonetaryAmount::fromCents(1500),
            shippingFeeTax: null,
            discountFee: null,
            discountFeeTax: null,
            total: MonetaryAmount::fromCents(10000),
            totalTax: null,
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: null,
            currencyCode: 'USD',
            paymentMethod: 'PayPal',
            paymentIssuer: 'paypal',
            transactionId: null,
            paymentIntentId: null,
            customerIpAddress: null,
            customerUserAgent: null,
            metaData: null,
            comments: null,
            promoCode: null,
            isPaid: false,
            paidAt: null,
            createdAt: null,
            updatedAt: null,
            uploads: $uploads,
            paymentFees: null,
        );

        $json = $dto->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertEquals(10, $decoded['customerId']);
        $this->assertEquals('etsy', $decoded['source']);
        $this->assertEquals(10000, $decoded['orderNumber']);
        $this->assertEquals('Bob', $decoded['firstName']);
        $this->assertEquals('USD', $decoded['currencyCode']);
    }

    #[Test]
    public function it_can_have_multiple_uploads(): void
    {
        $uploads = collect([
            $this->createUploadDTO(),
            $this->createUploadDTO(),
            $this->createUploadDTO(),
        ]);

        $dto = new OrderDTO(
            customerId: 1,
            customerStripeId: null,
            shopReceiptId: null,
            source: 'wp',
            wpId: 1,
            orderNumber: 1,
            orderKey: null,
            status: 'pending',
            firstName: 'Test',
            lastName: 'User',
            email: 'test@test.com',
            billingFirstName: 'Test',
            billingLastName: 'User',
            billingCompany: null,
            billingPhoneNumber: '123',
            billingEmail: 'test@test.com',
            billingAddressLine1: 'Test St',
            billingAddressLine2: null,
            billingPostalCode: '1234',
            billingCity: 'Test City',
            billingState: null,
            billingCountry: 'NL',
            billingVatNumber: null,
            shippingFirstName: 'Test',
            shippingLastName: 'User',
            shippingCompany: null,
            shippingPhoneNumber: null,
            shippingEmail: null,
            shippingAddressLine1: 'Test St',
            shippingAddressLine2: null,
            shippingPostalCode: '1234',
            shippingCity: 'Test City',
            shippingState: null,
            shippingCountry: 'NL',
            inCents: false,
            shippingFee: null,
            shippingFeeTax: null,
            discountFee: null,
            discountFeeTax: null,
            total: null,
            totalTax: null,
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: null,
            currencyCode: 'EUR',
            paymentMethod: 'Test',
            paymentIssuer: 'test',
            transactionId: null,
            paymentIntentId: null,
            customerIpAddress: null,
            customerUserAgent: null,
            metaData: null,
            comments: null,
            promoCode: null,
            isPaid: false,
            paidAt: null,
            createdAt: null,
            updatedAt: null,
            uploads: $uploads,
            paymentFees: null,
        );

        $this->assertCount(3, $dto->uploads);
        $this->assertInstanceOf(Collection::class, $dto->uploads);
    }

    #[Test]
    public function it_handles_refunds(): void
    {
        $uploads = collect([$this->createUploadDTO()]);

        $dto = new OrderDTO(
            customerId: 1,
            customerStripeId: null,
            shopReceiptId: null,
            source: 'wp',
            wpId: 1,
            orderNumber: 1,
            orderKey: null,
            status: 'refunded',
            firstName: 'Test',
            lastName: 'User',
            email: 'test@test.com',
            billingFirstName: 'Test',
            billingLastName: 'User',
            billingCompany: null,
            billingPhoneNumber: '123',
            billingEmail: 'test@test.com',
            billingAddressLine1: 'Test St',
            billingAddressLine2: null,
            billingPostalCode: '1234',
            billingCity: 'Test City',
            billingState: null,
            billingCountry: 'NL',
            billingVatNumber: null,
            shippingFirstName: 'Test',
            shippingLastName: 'User',
            shippingCompany: null,
            shippingPhoneNumber: null,
            shippingEmail: null,
            shippingAddressLine1: 'Test St',
            shippingAddressLine2: null,
            shippingPostalCode: '1234',
            shippingCity: 'Test City',
            shippingState: null,
            shippingCountry: 'NL',
            inCents: false,
            shippingFee: MonetaryAmount::fromFloat(10.00),
            shippingFeeTax: MonetaryAmount::fromFloat(2.10),
            discountFee: null,
            discountFeeTax: null,
            total: MonetaryAmount::fromFloat(60.00),
            totalTax: MonetaryAmount::fromFloat(12.60),
            totalRefund: MonetaryAmount::fromFloat(60.00),
            totalRefundTax: MonetaryAmount::fromFloat(12.60),
            taxPercentage: 21.0,
            currencyCode: 'EUR',
            paymentMethod: 'iDEAL',
            paymentIssuer: 'ideal',
            transactionId: null,
            paymentIntentId: null,
            customerIpAddress: null,
            customerUserAgent: null,
            metaData: null,
            comments: null,
            promoCode: null,
            isPaid: true,
            paidAt: Carbon::now()->subDay(),
            createdAt: Carbon::now()->subDays(2),
            updatedAt: Carbon::now(),
            uploads: $uploads,
            paymentFees: null,
        );

        $this->assertEquals('refunded', $dto->status);
        $this->assertEquals(60.00, $dto->totalRefund->toFloat());
        $this->assertEquals(12.60, $dto->totalRefundTax->toFloat());
    }
}
