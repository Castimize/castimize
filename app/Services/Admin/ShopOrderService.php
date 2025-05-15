<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Shop;
use App\Models\ShopOrder;
use Codexshaper\WooCommerce\Models\Order;
use Etsy\Resources\Receipt;

class ShopOrderService
{
    public function createShopOrder(Shop $shop, Receipt $receipt, $wcOrder): ShopOrder
    {
        return $shop->shopOrders()->create([
            'shop_owner_id' => $shop->shop_owner_id,
            'order_number' => $wcOrder['number'],
            'shop_receipt_id' => $receipt->receipt_id,
            'state' => $receipt->state,
        ]);
    }
}
