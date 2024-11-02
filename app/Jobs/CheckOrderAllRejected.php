<?php

namespace App\Jobs;

use App\Models\Order;
use Codexshaper\WooCommerce\Facades\Refund;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CheckOrderAllRejected implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(private Order $order)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $cacheKey = sprintf('create-order-all-rejected-job-%s', $this->order->id);
        $orderQueues = $this->order->orderQueues()->with(['rejection', 'upload'])->get();

        $refundAll = true;
        $refundAmount = 0.00;
        foreach ($orderQueues as $orderQueue) {
            if (!$orderQueue->rejection) {
                $refundAll = false;
            } else {
                $refundAmount = $orderQueue->rejection->amount;
            }
        }

        if ($refundAll) {
            $refundAmount = $this->order->total;
        }

        $data = [
            'amount' => (string)$refundAmount,
        ];

        \Codexshaper\WooCommerce\Facades\Order::createRefund($this->order->wp_id, $data);

        // ToDo: If no mail in woocommerce send email

        Cache::forget($cacheKey);
    }
}
