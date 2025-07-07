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
        WoocommerceApiService $woocommerceApiService,
        ShopOrderService $shopOrderService,
        StripeService $stripeService,
    ) {
        $date = now()->subHour();
        $shops = Shop::with(['shopOwner.customer'])->where('active', true)->where('shop', ShopOwnerShopsEnum::Etsy->value)->get();

        foreach ($shops as $shop) {
            try {
                $receipts = $etsyService->getShopReceipts($shop, ['min_created' => $date]);
                foreach ($receipts->data as $receipt) {
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
                                // Use mandate to pay the order
                                $wcOrder = $woocommerceApiService->createOrder($orderDTO);
                                $orderDTO->orderNumber = $wcOrder->order_number;
                                $shopOrderService->createShopOrder($shop, $receipt, $wcOrder);

                                $paymentIntent = $stripeService->createPaymentIntent($orderDTO, $shop->shopOwner->customer);
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
                            } catch (Exception $e) {
                                if ($wcOrder !== null) {
                                    $woocommerceApiService->deleteOrder((int)$wcOrder['id']);
                                }

                                DB::rollBack();
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine());
            }
        }

        return true;
    }
}
