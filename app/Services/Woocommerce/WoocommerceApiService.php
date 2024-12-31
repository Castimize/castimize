<?php

namespace App\Services\Woocommerce;

use Codexshaper\WooCommerce\Facades\Order;

class WoocommerceApiService
{
    public function updateOrderStatus(int $wpOrderId, string $status)
    {
        $data = [
            'status' => $status,
        ];

        return Order::update($wpOrderId, $data);
    }

    public function refundOrder(int $wpOrderId, string $refundAmount, array $lineItems = [])
    {
        $data = [
            'amount' => $refundAmount,
        ];

        if (!empty($lineItems)) {
            $data['line_items'] = $lineItems;
        }

        return null;

//        return Order::createRefund($wpOrderId, $data);
    }
}
