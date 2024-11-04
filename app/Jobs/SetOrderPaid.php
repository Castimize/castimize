<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\Admin\LogRequestService;
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

    /**
     * Create a new job instance.
     */
    public function __construct(public PaymentIntent $paymentIntent, public ?int $logRequestId = null)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $order = Order::with(['uploads'])
            ->where('order_number', $this->paymentIntent->metadata->order_id)
            ->first();

        if ($order === null) {
            $this->release($this->timeout);
            return;
        }

        try {
            $order->status = 'processing';
            $order->is_paid = true;
            $order->paid_at = Carbon::createFromTimestamp($this->paymentIntent->created, 'GMT')?->setTimezone(env('APP_TIMEZONE'))->format('Y-m-d H:i:s');
            $order->save();

            $uploadsService = new UploadsService();
            foreach ($order->uploads as $upload) {
                // Set upload to order queue
                $uploadsService->setUploadToOrderQueue($upload);
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
