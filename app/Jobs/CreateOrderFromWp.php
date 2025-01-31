<?php

namespace App\Jobs;

use App\Dtos\Order\OrderDto;
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
    public function __construct(public OrderDto $orderDto, public ?int $logRequestId = null)
    {
        $this->ordersService = new OrdersService();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::where('wp_id', $this->orderDto->wpId)->first();

        if ($order !== null) {
            return;
        }

        try {
            $this->ordersService->storeOrderFromDto($this->orderDto);
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
