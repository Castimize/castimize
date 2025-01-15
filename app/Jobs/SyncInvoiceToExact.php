<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Invoice;
use App\Services\Admin\LogRequestService;
use App\Services\Exact\ExactOnlineService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncInvoiceToExact implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice, public int $wpCustomerId, protected $removeOld = false, public ?int $logRequestId = null)
    {
    }

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
            if ($customer->exact_online_guid === null) {
                throw new Exception('Customer exact_online_guid is null');
            }

//            if ($this->removeOld) {
//                $exactOnlineService->deleteSyncedInvoice($this->invoice);
//                //sleep(5);
//            }

//            $exactOnlineService->syncInvoice($this->invoice);
            if ($this->invoice->paid) {
//                sleep(2);
                $exactOnlineService->syncInvoicePaid($this->invoice);
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $this->invoice);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }
}
