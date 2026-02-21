<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SetOrderCanceled;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Manufacturer;
use App\Models\Material;
use App\Models\Order;
use App\Models\ShippingFee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Stripe\PaymentIntent;
use Tests\TestCase;

class SetOrderCanceledTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    private Currency $currency;

    private Customer $customer;

    private Manufacturer $manufacturer;

    private ShippingFee $shippingFee;

    private Material $material;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );

        $this->customer = Customer::factory()->create();
        $this->manufacturer = Manufacturer::factory()->create();
        $this->material = Material::factory()->create();

        $this->shippingFee = ShippingFee::factory()->create([
            'currency_id' => $this->currency->id,
        ]);

        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'currency_id' => $this->currency->id,
            'order_number' => 55555,
            'status' => 'processing',
        ]);
    }

    #[Test]
    public function it_has_correct_retry_configuration(): void
    {
        $paymentIntent = $this->createMockPaymentIntent();

        $job = new SetOrderCanceled($paymentIntent);

        $this->assertEquals(5, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    #[Test]
    public function it_accepts_log_request_id(): void
    {
        $paymentIntent = $this->createMockPaymentIntent();
        $logRequestId = 456;

        $job = new SetOrderCanceled($paymentIntent, $logRequestId);

        $this->assertEquals(456, $job->logRequestId);
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        $paymentIntent = $this->createMockPaymentIntent();

        SetOrderCanceled::dispatch($paymentIntent);

        Queue::assertPushed(SetOrderCanceled::class);
    }

    private function createMockPaymentIntent(int $orderId = 55555): PaymentIntent
    {
        $paymentIntent = new PaymentIntent('pi_test_cancel');
        $paymentIntent->metadata = (object) ['order_id' => $orderId];

        return $paymentIntent;
    }
}
