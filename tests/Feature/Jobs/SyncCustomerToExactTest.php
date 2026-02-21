<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\SyncCustomerToExact;
use App\Models\Customer;
use App\Services\Exact\ExactOnlineService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncCustomerToExactTest extends TestCase
{
    use DatabaseTransactions;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create([
            'wp_id' => 44444,
        ]);
    }

    #[Test]
    public function it_can_be_dispatched_to_queue(): void
    {
        Queue::fake();

        SyncCustomerToExact::dispatch(44444);

        Queue::assertPushed(SyncCustomerToExact::class, function ($job) {
            return $job->wpCustomerId === 44444;
        });
    }

    #[Test]
    public function it_has_correct_retry_configuration(): void
    {
        $job = new SyncCustomerToExact(44444);

        $this->assertEquals(5, $job->tries);
        $this->assertEquals(120, $job->timeout);
    }

    #[Test]
    public function it_skips_when_customer_not_found_in_database(): void
    {
        $exactOnlineService = $this->mock(ExactOnlineService::class);
        $exactOnlineService->shouldNotReceive('syncCustomer');

        $job = new SyncCustomerToExact(99999);
        $job->handle($exactOnlineService);

        // Job should complete without calling service
        $this->assertTrue(true);
    }

    #[Test]
    public function it_accepts_log_request_id(): void
    {
        $job = new SyncCustomerToExact(44444, 999);

        $this->assertEquals(999, $job->logRequestId);
    }
}
