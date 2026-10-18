<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SyncInvoicePaidToExact;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Exact\ExactOnlineService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Picqer\Financials\Exact\ApiException;
use Tests\TestCase;

class SyncInvoicePaidToExactTest extends TestCase
{
    use DatabaseTransactions;

    private Customer $customer;

    private Invoice $invoice;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create([
            'wp_id' => 55555,
            'exact_online_guid' => 'test-guid-123',
        ]);

        $this->invoice = Invoice::create([
            'customer_id' => $this->customer->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now(),
            'debit' => true,
            'total' => 100.00,
            'total_tax' => 21.00,
            'currency_code' => 'EUR',
            'paid' => true,
            'paid_at' => now(),
        ]);
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        SyncInvoicePaidToExact::dispatch($this->invoice, 55555);

        Queue::assertPushed(SyncInvoicePaidToExact::class, function ($job) {
            return $job->wpCustomerId === 55555;
        });
    }

    #[Test]
    public function it_has_correct_retry_configuration(): void
    {
        $job = new SyncInvoicePaidToExact($this->invoice, 55555);

        $this->assertEquals(5, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    #[Test]
    public function it_skips_when_customer_not_found_in_database(): void
    {
        $exactOnlineService = $this->mock(ExactOnlineService::class);
        $exactOnlineService->shouldNotReceive('syncInvoicePaid');

        $job = new SyncInvoicePaidToExact($this->invoice, 99999);
        $job->handle($exactOnlineService);

        $this->assertTrue(true);
    }

    #[Test]
    public function it_throws_exception_when_customer_has_no_exact_guid(): void
    {
        Customer::factory()->create([
            'wp_id' => 66666,
            'exact_online_guid' => null,
        ]);

        $exactOnlineService = $this->mock(ExactOnlineService::class);
        $exactOnlineService->shouldNotReceive('syncInvoicePaid');

        $job = new SyncInvoicePaidToExact($this->invoice, 66666);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Customer exact_online_guid is null');

        $job->handle($exactOnlineService);
    }

    #[Test]
    public function it_propagates_non_rate_limit_exceptions_from_sync_so_job_can_retry(): void
    {
        $exactOnlineService = $this->mock(ExactOnlineService::class);
        $exactOnlineService->shouldReceive('syncInvoicePaid')
            ->once()
            ->andThrow(new ApiException('Error 500: Internal Server Error'));

        $job = new SyncInvoicePaidToExact($this->invoice, 55555);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Error 500');

        $job->handle($exactOnlineService);
    }

    #[Test]
    public function it_does_not_throw_when_exact_returns_rate_limit_error(): void
    {
        $exactOnlineService = $this->mock(ExactOnlineService::class);
        $exactOnlineService->shouldReceive('syncInvoicePaid')
            ->once()
            ->andThrow(new ApiException('Error 429: Too Many Requests'));

        $job = new SyncInvoicePaidToExact($this->invoice, 55555);

        // Should not throw — job catches 429 and releases back to queue
        $job->handle($exactOnlineService);

        $this->assertTrue(true);
    }
}
