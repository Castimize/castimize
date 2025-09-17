<?php

namespace App\Console\Commands;

use App\DTO\Order\OrderDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\Admin\OrdersService;
use App\Services\Admin\ShopOrderService;
use App\Services\Etsy\EtsyService;
use App\Services\Payment\Stripe\StripeService;
use App\Services\Woocommerce\WoocommerceApiService;
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GetEtsyReceipts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'castimize:get-etsy-receipts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get Etsy receipts from shops and send to Wordpress';

    /**
     * Execute the console command.
     */
    public function handle(
        EtsyService $etsyService,
        OrdersService $ordersService,
        WoocommerceApiService $woocommerceApiService,
        ShopOrderService $shopOrderService,
        StripeService $stripeService,
    ) {
        $date = now()->subDays(14);
        $shops = Shop::with(['shopOwner.customer'])->where('id', 2)->where('active', true)->where('shop', ShopOwnerShopsEnum::Etsy->value)->get();

        foreach ($shops as $shop) {
            try {
                $receipts = $etsyService->getShopReceipts($shop, [
                    'min_created' => $date,
                ]);
                $this->info(sprintf('Found %s receipts for %s', $receipts->count(), $shop->id));
                foreach ($receipts->data as $receipt) {
                    $this->info(sprintf('Receipt %s', $receipt->receipt_id));
                    // Check if order already in shop_orders
                    $shopOrder = ShopOrder::where('shop_receipt_id', $receipt->receipt_id)->first();
                    if ($shopOrder === null) {
                        $lines = $etsyService->getShopListingsFromReceipt($shop, $receipt);
                        if (count($lines) > 0) {
                            DB::beginTransaction();
                            $wcOrder = null;
                            try {
                                // Create OrderDTO from Etsy receipt
                                $orderDTO = OrderDTO::fromEtsyReceipt($shop, $receipt, $lines);
//                                dd($orderDTO);
                                // Use mandate to pay the order
                                $wcOrder = $woocommerceApiService->createOrder($orderDTO);
                                $this->info('Woocommerce order created with id: ' . $wcOrder['id']);

                                $orderDTO->orderNumber = (int) $wcOrder['number'];
                                $orderDTO->wpId = (int) $wcOrder['id'];

                                $order = $ordersService->storeOrderFromDto($orderDTO);
                                $this->info('Castimize order created with id: ' . $order->id);

                                $newShopOrder = $shopOrderService->createShopOrder($shop, $receipt, $wcOrder);
                                $this->info('Shop order created with id: ' . $newShopOrder->id);

                                $paymentIntent = $stripeService->createPaymentIntent($orderDTO, $shop->shopOwner->customer);
                                $this->info('Payment intent: ' . print_r($paymentIntent, true));
                                if ($paymentIntent->status === 'succeeded') {
                                    $orderDTO->isPaid = true;
                                    $orderDTO->paidAt = Carbon::createFromTimestamp($paymentIntent->created, 'GMT')?->setTimezone(env('APP_TIMEZONE'))->format('Y-m-d H:i:s');
                                }
                                $orderDTO->metaData[] = [
                                    'key' => '_payment_intent_id',
                                    'value' => $paymentIntent->id,
                                ];
                                $orderDTO->metaData[] = [
                                    'key' => '_payment_method_token',
                                    'value' => $paymentIntent->payment_method,
                                ];
                                $woocommerceApiService->updateOrder($orderDTO);

                                DB::commit();

                                $this->info(sprintf('Processed Receipt %s', $receipt->receipt_id));
                            } catch (Exception $e) {
                                if ($wcOrder !== null) {
                                    $woocommerceApiService->deleteOrder((int) $wcOrder['id']);
                                }

                                DB::rollBack();
                                dd($e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getTraceAsString());
                            }
                        }
                    } else {
                        $this->info('Shop order found with id: ' . $shopOrder->id);
                    }
                }
            } catch (Exception $e) {
                Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine());
            }
        }

        return true;
    }
}
