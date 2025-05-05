<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Admin\OrderQueuesService;
use App\Services\Admin\OrdersService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CheckOrderAllRejected implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Order $order,
    ) {
    }

    public function handle(): void
    {
        $cacheKey = sprintf('create-order-all-rejected-job-%s', $this->order->id);
        $orderQueues = $this->order->orderQueues()->with(['rejection', 'upload'])->get();

        $orderQueuesService = new OrderQueuesService();
        $toRejectOrderQueues = [];
        foreach ($orderQueues as $orderQueue) {
            if ($orderQueue->rejection) {
                $toRejectOrderQueues[] = $orderQueue;
                $orderQueuesService->setStatus($orderQueue, 'canceled');
            }
        }

        if (!$this->order->has_manual_refund) {
            $ordersService = new OrdersService();
            $ordersService->handleRejectionsAndRefund($this->order, $toRejectOrderQueues);
        }

        // ToDo: If no mail in woocommerce send email

        Cache::forget($cacheKey);
    }
}
