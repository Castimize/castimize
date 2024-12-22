<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Exact\ExactOnlineService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncInvoiceToExact implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 120;

    private ExactOnlineService $exactOnlineService;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $wpCustomerId, public int $wpOrderId, public ?int $logRequestId = null)
    {
        $this->exactOnlineService = new ExactOnlineService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $customer = Customer::where('wp_id', $this->wpCustomerId)->first();
        $order = Order::where('wp_id', $this->wpOrderId)->first();
        $invoice = null;

        if ($customer === null || $order === null) {
            return;
        }

        try {
            $invoiceNumber = null;
            foreach ($order->meta_data as $metaData) {
                if ($metaData->key === '_wcpdf_invoice_number') {
                    $invoiceNumber = $metaData->value;
                }
            }

            if ($invoiceNumber === null) {
                return;
            }

            $invoice = Invoice::with('lines')
                ->where('customer_id', $customer->id)
                ->where('invoice_number', $invoiceNumber)
                ->first();
            if ($invoice === null) {
                return;
            }
            $this->exactOnlineService->syncInvoice($invoice);
        } catch (Throwable $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $invoice);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }
}
