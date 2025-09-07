<?php

namespace App\Jobs;

use App\DTO\Order\OrderDTO;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\OrdersService;
use App\Services\Mail\MailgunService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateOrderFromDTO implements ShouldQueue
{
    use Queueable;

    public $tries = 5;
    public $timeout = 120;

    private OrdersService $ordersService;

    /**
     * Create a new job instance.
     */
    public function __construct(public OrderDTO $orderDto, public ?int $logRequestId = null)
    {
        $this->ordersService = new OrdersService();
    }

    /**
     * Execute the job.
     */
    public function handle(MailgunService $mailgunService): void
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
            $title = 'Order creation failed for order number: ' . $this->orderDto->orderNumber;
            $mailgunService->send(
                to: config('mail.from.address'),
                subject: $title,
                parameters: [
                    'template' => 'order creation failed',
                    'cc' => 'matthijs.bon1@gmail.com',
                    'v:title' => $title,
                    'v:order_number' => $this->orderDto->orderNumber,
                    'v:error_message' => $exception->getMessage(),
                    'v:error_file' => $exception->getFile(),
                    'v:error_line' => $exception->getLine(),
                ],
            );
        }
    }
}
