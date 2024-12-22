<?php

namespace App\Jobs;

use App\Models\Customer;
use App\Models\Order;
use App\Services\Admin\InvoicesService;
use App\Services\Admin\LogRequestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateInvoiceFromOrder implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 120;

    private InvoicesService $invoicesService;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $wpCustomerId, public int $wpOrderId, public ?int $logRequestId = null)
    {
        $this->invoicesService = new InvoicesService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::where('wp_id', $this->wpOrderId)->first();
        $customer = Customer::where('wp_id', $this->wpCustomerId)->first();

        if ($order === null) {
            return;
        }

        try {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($this->wpOrderId);
            if ($wpOrder === null) {
                return;
            }
            $order->wpOrder = $wpOrder;
            $this->invoicesService->storeInvoiceFromWpOrder($customer, $order);
        } catch (Throwable $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $order);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
    }
}
