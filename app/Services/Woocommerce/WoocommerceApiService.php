<?php

namespace App\Services\Woocommerce;

use Codexshaper\WooCommerce\Facades\Order;

class WoocommerceApiService
{
    /**
     * @param int $wpOrderId
     * @param string $status
     * @return mixed
     */
    public function updateOrderStatus(int $wpOrderId, string $status)
    {
        $data = [
            'status' => $status,
        ];

        return Order::update($wpOrderId, $data);
    }
}
