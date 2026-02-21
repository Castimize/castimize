<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\DTO\Order\OrderDTO;
use App\Helpers\MonetaryAmount;
use App\Jobs\UpdateOrderFromDTO;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateOrderFromDTOTest extends TestCase
{
    use DatabaseTransactions;

    private Customer $customer;

    private Currency $currency;

    private Country $country;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );

        $this->country = Country::firstOrCreate(
            ['alpha2' => 'NL'],
            [
                'name' => 'Netherlands',
                'alpha3' => 'NLD',
                'numeric' => 528,
            ]
        );

        $this->customer = Customer::factory()->create([
            'wp_id' => 12345,
        ]);

        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'wp_id' => 77777,
            'order_number' => 77777,
            'currency_id' => $this->currency->id,
            'email' => 'original@example.com',
            'status' => 'pending',
        ]);
    }

    #[Test]
    public function it_creates_job_with_dto(): void
    {
        $orderDto = $this->createOrderDTO(wpId: 77777, email: 'updated@example.com');

        $job = new UpdateOrderFromDTO($orderDto);

        $this->assertEquals(77777, $job->orderDto->wpId);
        $this->assertEquals('updated@example.com', $job->orderDto->email);
    }

    #[Test]
    public function it_skips_update_if_order_does_not_exist(): void
    {
        $orderDto = $this->createOrderDTO(wpId: 99999);

        $job = new UpdateOrderFromDTO($orderDto);
        $job->handle();

        // No exception should be thrown, job should complete silently
        $this->assertDatabaseMissing('orders', ['wp_id' => 99999]);
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        $orderDto = $this->createOrderDTO(wpId: 77777);

        UpdateOrderFromDTO::dispatch($orderDto);

        Queue::assertPushed(UpdateOrderFromDTO::class, function ($job) {
            return $job->orderDto->wpId === 77777;
        });
    }

    #[Test]
    public function it_has_correct_retry_configuration(): void
    {
        $orderDto = $this->createOrderDTO();

        $job = new UpdateOrderFromDTO($orderDto);

        $this->assertEquals(5, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    #[Test]
    public function it_accepts_log_request_id(): void
    {
        $orderDto = $this->createOrderDTO(wpId: 77777);
        $logRequestId = 123;

        $job = new UpdateOrderFromDTO($orderDto, $logRequestId);

        $this->assertEquals(123, $job->logRequestId);
    }

    private function createOrderDTO(int $wpId = 99999, string $email = 'test@example.com'): OrderDTO
    {
        return new OrderDTO(
            customerId: $this->customer->wp_id,
            customerStripeId: null,
            shopReceiptId: null,
            source: 'wp',
            wpId: $wpId,
            orderNumber: $wpId,
            orderKey: 'wc_order_test123',
            status: 'processing',
            firstName: 'John',
            lastName: 'Doe',
            email: $email,
            billingFirstName: 'John',
            billingLastName: 'Doe',
            billingCompany: null,
            billingPhoneNumber: '+31612345678',
            billingEmail: $email,
            billingAddressLine1: '123 Test Street',
            billingAddressLine2: null,
            billingPostalCode: '1234AB',
            billingCity: 'Amsterdam',
            billingState: 'NH',
            billingCountry: 'NL',
            billingVatNumber: null,
            shippingFirstName: 'John',
            shippingLastName: 'Doe',
            shippingCompany: null,
            shippingPhoneNumber: '+31612345678',
            shippingEmail: $email,
            shippingAddressLine1: '123 Test Street',
            shippingAddressLine2: null,
            shippingPostalCode: '1234AB',
            shippingCity: 'Amsterdam',
            shippingState: 'NH',
            shippingCountry: 'NL',
            inCents: false,
            shippingFee: MonetaryAmount::fromFloat(10.00),
            shippingFeeTax: MonetaryAmount::fromFloat(2.10),
            discountFee: MonetaryAmount::fromFloat(0),
            discountFeeTax: MonetaryAmount::fromFloat(0),
            total: MonetaryAmount::fromFloat(100.00),
            totalTax: MonetaryAmount::fromFloat(21.00),
            totalRefund: null,
            totalRefundTax: null,
            taxPercentage: 21.0,
            currencyCode: 'EUR',
            paymentMethod: 'stripe',
            paymentIssuer: 'ideal',
            transactionId: null,
            paymentIntentId: 'pi_test123',
            customerIpAddress: '127.0.0.1',
            customerUserAgent: 'Test Agent',
            metaData: [],
            comments: null,
            promoCode: null,
            isPaid: true,
            paidAt: now(),
            createdAt: now(),
            updatedAt: now(),
            uploads: collect([]),
            paymentFees: null,
        );
    }
}
