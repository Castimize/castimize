<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\CreateInvoicesFromOrder;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\Order;
use App\Services\Admin\InvoicesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateInvoicesFromOrderTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    private Customer $customer;

    private Currency $currency;

    protected function setUp(): void
    {
        parent::setUp();

        $this->currency = Currency::firstOrCreate(
            ['code' => 'EUR'],
            ['name' => 'Euro']
        );

        $this->customer = Customer::factory()->create();

        $this->order = Order::factory()->create([
            'customer_id' => $this->customer->id,
            'currency_id' => $this->currency->id,
            'wp_id' => 66666,
            'paid_at' => now(),
        ]);
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        CreateInvoicesFromOrder::dispatch(66666);

        Queue::assertPushed(CreateInvoicesFromOrder::class, function ($job) {
            return $job->wpOrderId === 66666;
        });
    }

    #[Test]
    public function it_has_correct_retry_configuration(): void
    {
        $job = new CreateInvoicesFromOrder(66666);

        $this->assertEquals(5, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    #[Test]
    public function it_skips_when_order_not_found(): void
    {
        $invoicesService = $this->mock(InvoicesService::class);
        $invoicesService->shouldNotReceive('storeInvoiceFromWpOrder');

        $job = new CreateInvoicesFromOrder(99999);
        $job->handle($invoicesService);

        // Job should complete without calling service
        $this->assertTrue(true);
    }

    #[Test]
    public function it_skips_when_order_not_paid(): void
    {
        Order::factory()->create([
            'customer_id' => $this->customer->id,
            'currency_id' => $this->currency->id,
            'wp_id' => 77777,
            'paid_at' => null,
        ]);

        $invoicesService = $this->mock(InvoicesService::class);
        $invoicesService->shouldNotReceive('storeInvoiceFromWpOrder');

        $job = new CreateInvoicesFromOrder(77777);
        $job->handle($invoicesService);

        // Job should complete without calling service
        $this->assertTrue(true);
    }

    #[Test]
    public function it_accepts_log_request_id(): void
    {
        $logRequestId = 789;

        $job = new CreateInvoicesFromOrder(66666, $logRequestId);

        $this->assertEquals(789, $job->logRequestId);
    }
}
