<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\OrdersService;
use App\Services\Admin\UploadsService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Throwable;

class SetOrderPaid implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 120;

    private OrdersService $ordersService;

    private UploadsService $uploadsService;

    /**
     * Create a new job instance.
     */
    public function __construct(public PaymentIntent $paymentIntent, public ?int $logRequestId = null)
    {
        $this->ordersService = new OrdersService();
        $this->uploadsService = app(UploadsService::class);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $order = Order::with(['uploads'])
                ->where('wp_id', $this->paymentIntent->metadata->order_id)
                ->first();

            if ($order === null) {
                $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($this->paymentIntent->metadata->order_id);
                if ($wpOrder === null) {
                    return;
                }
                $order = $this->ordersService->storeOrderFromWpOrder($wpOrder);
            }

            $order->status = 'processing';
            $order->is_paid = true;
            $order->paid_at = Carbon::createFromTimestamp($this->paymentIntent->created, 'GMT')?->setTimezone(env('APP_TIMEZONE'))->format('Y-m-d H:i:s');
            $order->save();

            foreach ($order->uploads as $upload) {
                // Set upload to order queue
                if ($upload->orderQueue === null) {
                    $this->uploadsService->setUploadToOrderQueue($upload);
                }
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
