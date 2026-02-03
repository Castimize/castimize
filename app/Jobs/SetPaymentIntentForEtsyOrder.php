<?php

namespace App\Jobs;

use App\DTO\Order\OrderDTO;
use App\Enums\Admin\PaymentMethodsEnum;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\PaymentService;
use App\Services\Woocommerce\WoocommerceApiService;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;
use Throwable;

class SetPaymentIntentForEtsyOrder implements ShouldQueue
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
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        WoocommerceApiService $woocommerceApiService,
        PaymentService $paymentService,
    ): void {
        $order = null;
        try {
            if (isset($this->paymentIntent->metadata->source) && $this->paymentIntent->metadata->source === 'Etsy API') {
                $order = Order::with(['uploads'])
                    ->where('wp_id', $this->paymentIntent->metadata->order_id)
                    ->first();

                if ($order !== null) {
                    $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($this->paymentIntent->metadata->order_id);
                    if ($wpOrder === null) {
                        return;
                    }

                    $paymentMethod = $paymentService->getStripePaymentMethod($this->paymentIntent->payment_method);
                    $charge = $paymentService->getStripeCharge($this->paymentIntent->latest_charge);
                    $balanceTransaction = $paymentService->getStripeBalanceTransaction($charge->balance_transaction);

                    $metaData = $wpOrder['meta_data'];
                    $metaData[] = [
                        'key' => '_payment_intent_id',
                        'value' => $this->paymentIntent->id,
                    ];
                    $metaData[] = [
                        'key' => '_payment_method_token',
                        'value' => $this->paymentIntent->payment_method,
                    ];
                    $metaData[] = [
                        'key' => '_stripe_currency',
                        'value' => $this->paymentIntent->currency,
                    ];
                    $metaData[] = [
                        'key' => '_stripe_fee',
                        'value' => (string) ($balanceTransaction->fee / 100),
                    ];
                    $metaData[] = [
                        'key' => '_stripe_net',
                        'value' => (string) ($balanceTransaction->amount / 100),
                    ];

                    $request = new Request;
                    $request->replace(['id' => $this->paymentIntent->metadata->order_id]);

                    $orderDTO = OrderDTO::fromWpRequest($request);
                    $orderDTO->transactionId = $this->paymentIntent->latest_charge;
                    $orderDTO->paymentIntentId = $this->paymentIntent->id;
                    $orderDTO->paymentMethod = PaymentMethodsEnum::getWoocommercePaymentMethod($paymentMethod?->type);
                    $orderDTO->paymentIssuer = PaymentMethodsEnum::getWoocommercePaymentMethod($paymentMethod?->type);
                    $orderDTO->metaData = $metaData;
                    $orderDTO->isPaid = true;
                    $orderDTO->paidAt = Carbon::createFromTimestamp($this->paymentIntent->created, 'GMT')
                        ?->setTimezone(config('app.timezone'));
                    $woocommerceApiService->updateOrder($orderDTO);

                    $order->payment_intent_id = $orderDTO->paymentIntentId;
                    $order->payment_method = $orderDTO->paymentMethod;
                    $order->payment_issuer = $orderDTO->paymentIssuer;
                    $order->meta_data = $metaData;
                    $order->save();
                }
            }
        } catch (Throwable $e) {
            Log::channel('etsy')->error($e->getMessage().PHP_EOL.$e->getTraceAsString());
        }

        try {
            LogRequestService::addResponseById($this->logRequestId, $order);
        } catch (Throwable $exception) {
            Log::channel('etsy')->error($exception->getMessage().PHP_EOL.$exception->getTraceAsString());
        }
    }
}
