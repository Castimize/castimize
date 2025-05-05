<?php

namespace App\Console\Commands;

use App\DTO\Order\OrderDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Shop;
use App\Services\Admin\ShopOrderService;
use App\Services\Etsy\EtsyService;
use App\Services\Woocommerce\WoocommerceApiService;
use Illuminate\Console\Command;

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
    public function handle(EtsyService $etsyService, WoocommerceApiService $woocommerceApiService, ShopOrderService $shopOrderService)
    {
        $date = now()->subHour();
        $shops = Shop::with(['shopOwner.customer'])->where('active', true)->where('shop', ShopOwnerShopsEnum::Etsy->value)->get();

        foreach ($shops as $shop) {
            $receipts = $etsyService->getShopReceipts($shop, ['min_created' => $date]);
            foreach ($receipts->data as $receipt) {
                $lines = $etsyService->getShopListingsFromReceipt($shop, $receipt);
                if (count($lines) > 0) {
                    $orderDTO = OrderDTO::fromEtsyReceipt($shop, $receipt, $lines);
                    $wcOrder = $woocommerceApiService->createOrder($orderDTO);
                    $shopOrderService->createShopOrder($shop, $receipt, $wcOrder);
                }
            }
        }

        return true;
    }
}
