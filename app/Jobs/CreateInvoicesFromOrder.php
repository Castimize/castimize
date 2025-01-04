<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Order;
use App\Services\Admin\InvoicesService;
use App\Services\Admin\LogRequestService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateInvoicesFromOrder implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $wpOrderId, public ?int $logRequestId = null)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(InvoicesService $invoicesService): void
    {
        $order = Order::with('customer')->where('wp_id', $this->wpOrderId)->first();
        $customer = $order->customer;

        if ($order === null) {
            return;
        }

        try {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($this->wpOrderId);
            if ($wpOrder === null) {
                return;
            }
            $order->wpOrder = $wpOrder;

            $invoiceDocument = Invoice::WOOCOMMERCE_DOCUMENT_INVOICE;
            if (isset($wpOrder['documents']->$invoiceDocument)) {
                $invoicesService->storeInvoiceFromWpOrder($customer, $order);
            }
            $creditNoteDocument = Invoice::WOOCOMMERCE_DOCUMENT_CREDIT_NOTE;
            if (isset($wpOrder['documents']->$creditNoteDocument)) {
                $invoicesService->storeInvoiceFromWpOrder($customer, $order, false);
            }

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