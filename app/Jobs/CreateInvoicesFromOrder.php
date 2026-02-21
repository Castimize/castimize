<?php

namespace App\Jobs;

use App\Enums\Woocommerce\WcOrderDocumentTypesEnum;
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
    public function __construct(
        public int $wpOrderId,
        public ?int $logRequestId = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(InvoicesService $invoicesService): void
    {
        $order = Order::with('customer')->where('wp_id', $this->wpOrderId)->first();
        if ($order === null) {
            Log::channel('orders')->info("CreateInvoicesFromOrder: Order not found for wp_id {$this->wpOrderId}");

            return;
        }
        if ($order->paid_at === null) {
            Log::channel('orders')->info("CreateInvoicesFromOrder: Order {$this->wpOrderId} has no paid_at date, skipping");

            return;
        }
        if (empty($order->payment_issuer)) {
            Log::channel('orders')->info("CreateInvoicesFromOrder: Order {$this->wpOrderId} has no payment_issuer, skipping invoice creation");

            return;
        }
        $customer = $order->customer;

        $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($this->wpOrderId);
        if ($wpOrder === null) {
            Log::channel('orders')->warning("CreateInvoicesFromOrder: WP Order not found for wp_id {$this->wpOrderId}");

            return;
        }
        $order->wpOrder = $wpOrder;

        $invoiceDocument = WcOrderDocumentTypesEnum::Invoice->value;
        if (property_exists($wpOrder['documents'], $invoiceDocument)) {
            Log::channel('orders')->info("CreateInvoicesFromOrder: Creating invoice for order {$this->wpOrderId}");
            $invoicesService->storeInvoiceFromWpOrder($customer, $order);
        }

        $creditNoteDocument = WcOrderDocumentTypesEnum::CreditNote->value;
        if (property_exists($wpOrder['documents'], $creditNoteDocument)) {
            Log::channel('orders')->info("CreateInvoicesFromOrder: Creating credit note for order {$this->wpOrderId}");
            $invoicesService->storeInvoiceFromWpOrder($customer, $order, false);
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $order);
        } catch (Throwable $exception) {
            Log::channel('orders')->error($exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        }
    }
}
