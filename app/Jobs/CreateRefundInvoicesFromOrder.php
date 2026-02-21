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

class CreateRefundInvoicesFromOrder implements ShouldQueue
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
            Log::channel('orders')->info("CreateRefundInvoicesFromOrder: Order not found for wp_id {$this->wpOrderId}");

            return;
        }
        if (empty($order->payment_issuer)) {
            Log::channel('orders')->info("CreateRefundInvoicesFromOrder: Order {$this->wpOrderId} has no payment_issuer, skipping");

            return;
        }
        $customer = $order->customer;

        try {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($this->wpOrderId);
            if ($wpOrder === null) {
                return;
            }
            $order->wpOrder = $wpOrder;

            $creditNoteDocument = WcOrderDocumentTypesEnum::CreditNote->value;
            if (property_exists($wpOrder['documents'], $creditNoteDocument)) {
                $invoicesService->storeInvoiceFromWpOrder($customer, $order, false);
            }

        } catch (Throwable $e) {
            Log::channel('orders')->error($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $order);
        } catch (Throwable $exception) {
            Log::channel('orders')->error($exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        }
    }
}
