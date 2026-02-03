<?php

namespace App\Console\Commands;

use App\DTO\Order\OrderDTO;
use App\Enums\Admin\PaymentMethodsEnum;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Order;
use App\Models\Shop;
use App\Models\ShopOrder;
use App\Services\Admin\OrdersService;
use App\Services\Admin\PaymentService;
use App\Services\Admin\ShopOrderService;
use App\Services\Etsy\EtsyService;
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
        PaymentService $paymentService,
    ): int {
        $minCreatedTimestamp = now()->subDays(14)->timestamp;
        $shops = Shop::with(['shopOwner.customer'])
            ->where('active', true)
            ->where('shop', ShopOwnerShopsEnum::Etsy->value)
            ->get();

        foreach ($shops as $shop) {
            try {
                $receipts = $etsyService->getShopReceipts($shop, [
                    'min_created' => $minCreatedTimestamp,
                ]);
                $this->info(sprintf('Found %s receipts for %s', $receipts->count(), $shop->id));
                foreach ($receipts->data as $receipt) {
                    $this->info(sprintf('Receipt %s', $receipt->receipt_id));
                    // Check if order already in shop_orders
                    $shopOrder = ShopOrder::where('shop_receipt_id', $receipt->receipt_id)->first();
                    if ($shopOrder === null) {
                        $lines = $etsyService->getShopListingsFromReceipt($shop, $receipt);
                        if (count($lines) > 0) {
                            $wcOrder = null;
                            try {
                                DB::beginTransaction();
                                // Create OrderDTO from Etsy receipt
                                $orderDTO = OrderDTO::fromEtsyReceipt($shop, $receipt, $lines);
                                // Use mandate to pay the order
                                $wcOrder = $woocommerceApiService->createOrder($orderDTO);
                                $this->info('Woocommerce order created with id: '.$wcOrder['id']);

                                $orderDTO->orderNumber = (int) $wcOrder['number'];
                                $orderDTO->wpId = (int) $wcOrder['id'];

                                // Check if order already exists (may have been created by WooCommerce webhook)
                                $order = Order::where('wp_id', $orderDTO->wpId)->first();
                                if ($order === null) {
                                    $order = $ordersService->storeOrderFromDto($orderDTO);
                                    $this->info('Castimize order created with id: '.$order->id);
                                } else {
                                    $this->info('Castimize order already exists with id: '.$order->id.' (created by webhook)');
                                }

                                $newShopOrder = $shopOrderService->createShopOrder($shop, $receipt, $wcOrder);
                                $this->info('Shop order created with id: '.$newShopOrder->id);

                                $paymentIntent = $paymentService->createStripePaymentIntent($orderDTO, $shop->shopOwner->customer);
                                $this->info('Payment intent: '.print_r($paymentIntent, true));

                                // Get the actual Stripe payment method type and map to WooCommerce payment issuer
                                $stripePaymentMethodType = $paymentIntent->payment_method_types[0] ?? 'card';
                                $paymentIssuer = PaymentMethodsEnum::getWoocommercePaymentMethod($stripePaymentMethodType);

                                // Update local order with correct Stripe payment issuer
                                $order->update([
                                    'payment_method' => 'Stripe',
                                    'payment_issuer' => $paymentIssuer,
                                    'payment_intent_id' => $paymentIntent->id,
                                ]);

                                if ($paymentIntent->status === 'succeeded') {
                                    $orderDTO->isPaid = true;
                                    $orderDTO->paidAt = Carbon::createFromTimestamp($paymentIntent->created, 'GMT')
                                        ?->setTimezone(config('app.timezone'));
                                    $order->update([
                                        'is_paid' => true,
                                        'paid_at' => $orderDTO->paidAt,
                                    ]);
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
                                Log::channel('etsy')->error("GetEtsyReceipts: Failed to process receipt {$receipt->receipt_id}: ".$e->getMessage().PHP_EOL.$e->getTraceAsString());
                                $this->error("Failed to process receipt {$receipt->receipt_id}: ".$e->getMessage());
                            }
                        } else {
                            $this->warn("Receipt {$receipt->receipt_id} has no matching listings, skipping");
                        }
                    } else {
                        $this->info('Shop order found with id: '.$shopOrder->id);
                    }
                }
            } catch (Exception $e) {
                $shopName = $shop->shop_oauth['shop_name'] ?? $shop->shop_oauth['shop_id'] ?? 'unknown';
                Log::channel('etsy')->error("GetEtsyReceipts failed for shop '{$shopName}' (ID: {$shop->id}): ".$e->getMessage().PHP_EOL.$e->getTraceAsString());
                $this->error("Failed for shop '{$shopName}' (ID: {$shop->id}): ".$e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}
