<?php

namespace App\Console\Commands\Temp\Etsy;

use App\DTO\Order\OrderDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\Enums\Admin\PaymentMethodsEnum;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Country;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\Admin\OrdersService;
use App\Services\Admin\PaymentService;
use App\Services\Admin\ShopOrderService;
use App\Services\Etsy\EtsyService;
use App\Services\Etsy\EtsyShippingProfileService;
use App\Services\Payment\Stripe\StripeService;
use App\Services\Woocommerce\WoocommerceApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class FixPaymentIntentFromEtsyOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:fix-payment-intent-from-etsy-order {--payment-intent-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix payment intent from Etsy order';

    /**
     * Execute the console command.
     */
    public function handle(
        PaymentService $paymentService,
        WoocommerceApiService $woocommerceApiService,
    ) {
        $paymentIntent = $paymentService->getStripePaymentIntent($this->option('payment-intent-id'));
        $charge = $paymentService->getStripeCharge($paymentIntent->latest_charge);
        $balanceTransaction = $paymentService->getStripeBalanceTransaction($charge->balance_transaction);

        try {
            if (isset($paymentIntent->metadata->source) && $paymentIntent->metadata->source === 'Etsy API') {
                $order = Order::with(['uploads'])
                    ->where('wp_id', $paymentIntent->metadata->order_id)
                    ->first();

                if ($order) {
                    $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($paymentIntent->metadata->order_id);
                    if ($wpOrder === null) {
                        return false;
                    }

                    $paymentMethod = $paymentService->getStripePaymentMethod($paymentIntent->payment_method);

                    $metaData = $wpOrder['meta_data'];
                    $metaData[] = [
                        'key' => '_payment_intent_id',
                        'value' => $paymentIntent->id,
                    ];
                    $metaData[] = [
                        'key' => '_payment_method_token',
                        'value' => $paymentIntent->payment_method,
                    ];
                    $metaData[] = [
                        'key' => '_stripe_currency',
                        'value' => $paymentIntent->currency,
                    ];
                    $metaData[] = [
                        'key' => '_stripe_fee',
                        'value' => (string) ($balanceTransaction->fee / 100),
                    ];
                    $metaData[] = [
                        'key' => '_stripe_net',
                        'value' => (string) ($balanceTransaction->amount / 100),
                    ];

                    $request = new Request();
                    $request->replace(['id' => $paymentIntent->metadata->order_id]);

                    $orderDTO = OrderDTO::fromWpRequest($request);
                    $orderDTO->transactionId = $paymentIntent->latest_charge;
                    $orderDTO->paymentIntentId = $paymentIntent->id;
                    $orderDTO->paymentMethod = PaymentMethodsEnum::getWoocommercePaymentMethod($paymentMethod?->type);
                    $orderDTO->paymentIssuer = PaymentMethodsEnum::getWoocommercePaymentMethod($paymentMethod?->type);
                    $orderDTO->metaData = $metaData;
                    $orderDTO->isPaid = true;

                    $woocommerceApiService->updateOrder($orderDTO);
                    $order->payment_intent_id = $orderDTO->paymentIntentId;
                    $order->payment_method = $orderDTO->paymentMethod;
                    $order->payment_issuer = $orderDTO->paymentIssuer;
                    $order->meta_data = $metaData;
                    $order->save();
                }
            }
        } catch (Throwable $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        return true;
    }
}
