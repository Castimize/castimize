<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Services\Admin\LogRequestService;
use App\Services\Exact\ExactOnlineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncCustomerToExact implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $wpCustomerId,
        public ?int $logRequestId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ExactOnlineService $exactOnlineService): void
    {
        $customer = Customer::where('wp_id', $this->wpCustomerId)->first();

        if ($customer === null) {
            return;
        }

        try {
            $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($this->wpCustomerId);
            if ($wpCustomer === null) {
                return;
            }
            $customer->wpCustomer = $wpCustomer;
            $exactOnlineService->syncCustomer($customer);
        } catch (Throwable $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $customer);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        }
    }
}
