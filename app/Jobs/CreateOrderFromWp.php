<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\OrdersService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateOrderFromWp implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    public $timeout = 120;

    private OrdersService $ordersService;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public int $wpOrderId,
        public ?int $logRequestId = null,
    ) {
        $this->ordersService = new OrdersService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::where('wp_id', $this->wpOrderId)->first();

        if ($order !== null) {
            return;
        }

        try {
            $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($this->wpOrderId);
            if ($wpOrder === null) {
                return;
            }
            $this->ordersService->storeOrderFromWpOrder($wpOrder);
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
