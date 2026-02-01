<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\OrderQueuesService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Throwable;

class SetOrderCanceled implements ShouldQueue
{
    use Queueable;

    public $tries = 5;

    public $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public PaymentIntent $paymentIntent,
        public ?int $logRequestId = null,
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $orderQueuesService = new OrderQueuesService;
        $order = Order::with(['uploads', 'orderQueues'])
            ->where('order_number', $this->paymentIntent->metadata->order_id)
            ->first();

        if ($order === null) {
            $this->release($this->timeout);
        }

        try {
            $order->status = 'canceled';
            $order->save();
            foreach ($order->orderQueues as $orderQueue) {
                $orderQueuesService->setStatus($orderQueue, 'canceled');
                $orderQueue->delete();
            }
            $order->delete();
        } catch (Throwable $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $this->fail($e->getMessage());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $order);
        } catch (Throwable $exception) {
            Log::error($exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        }
    }
}
