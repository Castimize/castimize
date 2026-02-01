<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Admin;

use App\Models\Customer;
use App\Services\Admin\PaymentService;
use App\Services\Payment\Stripe\StripeService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Stripe\BalanceTransaction;
use Stripe\Charge;
use Stripe\Mandate;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use DatabaseTransactions;

    private PaymentService $paymentService;

    private MockInterface $stripeServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripeServiceMock = Mockery::mock(StripeService::class);
        $this->paymentService = new PaymentService($this->stripeServiceMock);
    }

    #[Test]
    public function it_retrieves_stripe_charge(): void
    {
        $chargeId = 'ch_test123';
        $mockCharge = Mockery::mock(Charge::class);

        $this->stripeServiceMock
            ->shouldReceive('getCharge')
            ->with($chargeId)
            ->once()
            ->andReturn($mockCharge);

        $result = $this->paymentService->getStripeCharge($chargeId);

        $this->assertSame($mockCharge, $result);
    }

    #[Test]
    public function it_retrieves_stripe_balance_transaction(): void
    {
        $balanceTransactionId = 'txn_test123';
        $mockTransaction = Mockery::mock(BalanceTransaction::class);

        $this->stripeServiceMock
            ->shouldReceive('getBalanceTransaction')
            ->with($balanceTransactionId)
            ->once()
            ->andReturn($mockTransaction);

        $result = $this->paymentService->getStripeBalanceTransaction($balanceTransactionId);

        $this->assertSame($mockTransaction, $result);
    }

    #[Test]
    public function it_retrieves_stripe_payment_method(): void
    {
        $paymentMethodId = 'pm_test123';
        $mockPaymentMethod = Mockery::mock(PaymentMethod::class);

        $this->stripeServiceMock
            ->shouldReceive('getPaymentMethod')
            ->with($paymentMethodId)
            ->once()
            ->andReturn($mockPaymentMethod);

        $result = $this->paymentService->getStripePaymentMethod($paymentMethodId);

        $this->assertSame($mockPaymentMethod, $result);
    }

    #[Test]
    public function it_retrieves_stripe_payment_methods(): void
    {
        $mockPaymentIntent = Mockery::mock(PaymentIntent::class);

        $this->stripeServiceMock
            ->shouldReceive('getPaymentMethods')
            ->once()
            ->andReturn($mockPaymentIntent);

        $result = $this->paymentService->getStripePaymentMethods();

        $this->assertSame($mockPaymentIntent, $result);
    }

    #[Test]
    public function it_retrieves_stripe_setup_intent(): void
    {
        $setupIntentId = 'seti_test123';
        $mockSetupIntent = Mockery::mock(SetupIntent::class);

        $this->stripeServiceMock
            ->shouldReceive('getSetupIntent')
            ->with($setupIntentId)
            ->once()
            ->andReturn($mockSetupIntent);

        $result = $this->paymentService->getStripeSetupIntent($setupIntentId);

        $this->assertSame($mockSetupIntent, $result);
    }

    #[Test]
    public function it_retrieves_stripe_payment_intent(): void
    {
        $paymentIntentId = 'pi_test123';
        $mockPaymentIntent = Mockery::mock(PaymentIntent::class);

        $this->stripeServiceMock
            ->shouldReceive('getPaymentIntent')
            ->with($paymentIntentId)
            ->once()
            ->andReturn($mockPaymentIntent);

        $result = $this->paymentService->getStripePaymentIntent($paymentIntentId);

        $this->assertSame($mockPaymentIntent, $result);
    }

    #[Test]
    public function it_retrieves_stripe_mandate(): void
    {
        $mandateId = 'mandate_test123';
        $mockMandate = Mockery::mock(Mandate::class);

        $this->stripeServiceMock
            ->shouldReceive('getMandate')
            ->with($mandateId)
            ->once()
            ->andReturn($mockMandate);

        $result = $this->paymentService->getStripeMandate($mandateId);

        $this->assertSame($mockMandate, $result);
    }

    #[Test]
    public function it_attaches_payment_method_successfully(): void
    {
        $customer = Customer::factory()->create([
            'stripe_data' => ['stripe_id' => 'cus_test123'],
        ]);

        $paymentMethodId = 'pm_test123';

        $mockPaymentMethod = Mockery::mock(PaymentMethod::class);
        $mockPaymentMethod->shouldReceive('attach')
            ->with(['customer' => 'cus_test123'])
            ->once();

        $this->stripeServiceMock
            ->shouldReceive('getPaymentMethod')
            ->with($paymentMethodId)
            ->once()
            ->andReturn($mockPaymentMethod);

        $this->stripeServiceMock
            ->shouldReceive('createTestPaymentIntent')
            ->with(
                Mockery::on(fn ($c) => $c->id === $customer->id),
                $paymentMethodId
            )
            ->once()
            ->andReturn(['success' => true]);

        $this->paymentService->attachStripePaymentMethod($customer, $paymentMethodId);

        $customer->refresh();
        $this->assertEquals($paymentMethodId, $customer->stripe_data['payment_method']);
        $this->assertTrue($customer->stripe_data['payment_method_chargable']);
        $this->assertArrayHasKey('payment_method_accepted_at', $customer->stripe_data);
    }

    #[Test]
    public function it_throws_exception_when_payment_method_test_fails(): void
    {
        $customer = Customer::factory()->create([
            'stripe_data' => ['stripe_id' => 'cus_test123'],
        ]);

        $paymentMethodId = 'pm_test123';

        $mockPaymentMethod = Mockery::mock(PaymentMethod::class);
        $mockPaymentMethod->shouldReceive('attach')
            ->with(['customer' => 'cus_test123'])
            ->once();

        $this->stripeServiceMock
            ->shouldReceive('getPaymentMethod')
            ->with($paymentMethodId)
            ->once()
            ->andReturn($mockPaymentMethod);

        $this->stripeServiceMock
            ->shouldReceive('createTestPaymentIntent')
            ->once()
            ->andReturn([
                'success' => false,
                'message' => 'Card declined',
            ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Card declined');

        $this->paymentService->attachStripePaymentMethod($customer, $paymentMethodId);
    }

    #[Test]
    public function it_cancels_stripe_mandate_with_payment_method(): void
    {
        $customer = Customer::factory()->create([
            'stripe_data' => [
                'stripe_id' => 'cus_test123',
                'payment_method' => 'pm_test123',
                'mandate_id' => 'mandate_test123',
            ],
        ]);

        $mockPaymentMethod = Mockery::mock(PaymentMethod::class);

        $this->stripeServiceMock
            ->shouldReceive('getPaymentMethod')
            ->with('pm_test123')
            ->once()
            ->andReturn($mockPaymentMethod);

        $this->stripeServiceMock
            ->shouldReceive('detachPaymentMethod')
            ->with($mockPaymentMethod)
            ->once()
            ->andReturn($mockPaymentMethod);

        $this->paymentService->cancelStripeMandate($customer);

        $customer->refresh();
        $this->assertEquals(['stripe_id' => 'cus_test123'], $customer->stripe_data);
        $this->assertArrayNotHasKey('payment_method', $customer->stripe_data);
        $this->assertArrayNotHasKey('mandate_id', $customer->stripe_data);
    }

    #[Test]
    public function it_does_nothing_when_customer_has_no_stripe_data(): void
    {
        $customer = Customer::factory()->create([
            'stripe_data' => null,
        ]);

        $this->stripeServiceMock->shouldNotReceive('getPaymentMethod');
        $this->stripeServiceMock->shouldNotReceive('detachPaymentMethod');

        $this->paymentService->cancelStripeMandate($customer);

        $this->assertNull($customer->stripe_data);
    }

    #[Test]
    public function it_does_nothing_when_customer_has_no_payment_method_or_mandate(): void
    {
        $customer = Customer::factory()->create([
            'stripe_data' => ['stripe_id' => 'cus_test123'],
        ]);

        $this->stripeServiceMock->shouldNotReceive('getPaymentMethod');
        $this->stripeServiceMock->shouldNotReceive('detachPaymentMethod');

        $this->paymentService->cancelStripeMandate($customer);

        $this->assertEquals(['stripe_id' => 'cus_test123'], $customer->stripe_data);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
