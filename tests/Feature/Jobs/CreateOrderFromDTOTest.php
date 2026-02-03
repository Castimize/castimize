<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\DTO\Order\OrderDTO;
use App\Helpers\MonetaryAmount;
use App\Jobs\CreateOrderFromDTO;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Mail\MailgunService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateOrderFromDTOTest extends TestCase
{
    use DatabaseTransactions;

    private Customer $customer;

    private Currency $currency;

    private Country $country;

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
    }

    #[Test]
    public function it_creates_job_with_dto(): void
    {
        $orderDto = $this->createOrderDTO();

        $job = new CreateOrderFromDTO($orderDto);

        $this->assertEquals(99999, $job->orderDto->wpId);
        $this->assertEquals('test@example.com', $job->orderDto->email);
    }

    #[Test]
    public function it_skips_creation_if_order_already_exists(): void
    {
        Order::factory()->create([
            'wp_id' => 88888,
            'order_number' => 88888,
            'currency_id' => $this->currency->id,
        ]);

        $mailgunService = $this->mock(MailgunService::class);
        $mailgunService->shouldNotReceive('send');

        $orderDto = $this->createOrderDTO(wpId: 88888, orderNumber: 88888);

        $job = new CreateOrderFromDTO($orderDto);
        $job->handle($mailgunService);

        // Should still only have one order with this wp_id
        $this->assertCount(1, Order::where('wp_id', 88888)->get());
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        $orderDto = $this->createOrderDTO();

        CreateOrderFromDTO::dispatch($orderDto);

        Queue::assertPushed(CreateOrderFromDTO::class, function ($job) {
            return $job->orderDto->wpId === 99999;
        });
    }

    #[Test]
    public function it_has_correct_retry_configuration(): void
    {
        $orderDto = $this->createOrderDTO();

        $job = new CreateOrderFromDTO($orderDto);

        $this->assertEquals(5, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    private function createOrderDTO(int $wpId = 99999, int $orderNumber = 99999): OrderDTO
    {
        return new OrderDTO(
            customerId: $this->customer->wp_id,
            customerStripeId: null,
            shopReceiptId: null,
            source: 'wp',
            wpId: $wpId,
            orderNumber: $orderNumber,
            orderKey: 'wc_order_test123',
            status: 'processing',
            firstName: 'John',
            lastName: 'Doe',
            email: 'test@example.com',
            billingFirstName: 'John',
            billingLastName: 'Doe',
            billingCompany: null,
            billingPhoneNumber: '+31612345678',
            billingEmail: 'test@example.com',
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
            shippingEmail: 'test@example.com',
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
