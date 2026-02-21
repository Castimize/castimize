<?php

namespace App\Jobs;

use App\DTO\Order\OrderDTO;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\OrdersService;
use App\Services\Mail\MailgunService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
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
    public function __construct(
        public OrderDTO $orderDto,
        public ?int $logRequestId = null
    ) {
        $this->ordersService = new OrdersService;
    }

    /**
     * Execute the job.
     */
    public function handle(MailgunService $mailgunService): void
    {
        $order = null;

        try {
            DB::beginTransaction();
            $order = Order::where('wp_id', $this->orderDto->wpId)->first();

            if ($order !== null) {
                DB::rollBack();

                return;
            }

            $order = $this->ordersService->storeOrderFromDto($this->orderDto);
            DB::commit();
        } catch (QueryException $e) {
            DB::rollBack();
            if ($e->errorInfo[1] === 1062) {
                // Duplicate entry - order was already created by a concurrent job, silently ignore
                Log::channel('orders')->info('Duplicate order skipped: wp_id '.$this->orderDto->wpId);

                return;
            }
            Log::channel('orders')->error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $title = 'Order creation failed for order number: '.$this->orderDto->orderNumber;
            $mailgunService->send(
                to: config('mail.from.address'),
                subject: $title,
                parameters: [
                    'template' => 'order creation failed',
                    'cc' => 'matthijs.bon1@gmail.com',
                    'v:title' => $title,
                    'v:order_number' => $this->orderDto->orderNumber,
                    'v:error_message' => $e->getMessage(),
                    'v:error_file' => $e->getFile(),
                    'v:error_line' => $e->getLine(),
                ],
            );
        } catch (Throwable $e) {
            DB::rollBack();
            Log::channel('orders')->error($e->getMessage().PHP_EOL.$e->getTraceAsString());
            $title = 'Order creation failed for order number: '.$this->orderDto->orderNumber;
            $mailgunService->send(
                to: config('mail.from.address'),
                subject: $title,
                parameters: [
                    'template' => 'order creation failed',
                    'cc' => 'matthijs.bon1@gmail.com',
                    'v:title' => $title,
                    'v:order_number' => $this->orderDto->orderNumber,
                    'v:error_message' => $e->getMessage(),
                    'v:error_file' => $e->getFile(),
                    'v:error_line' => $e->getLine(),
                ],
            );
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $order);
        } catch (Throwable $exception) {
            Log::channel('orders')->error($exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        }
    }
}
