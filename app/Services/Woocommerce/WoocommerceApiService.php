<?php

namespace App\Services\Woocommerce;

use App\DTO\Order\OrderDTO;
use Codexshaper\WooCommerce\Facades\Order;

class WoocommerceApiService
{
    public function createOrder(OrderDTO $orderDTO)
    {
        $data = [
            //'status' => $orderDTO->status,
            'customer_id' => $orderDTO->customerId,
            'set_paid' => $orderDTO->isPaid,
            'billing' => [
                'first_name' => $orderDTO->billingFirstName,
                'last_name' => $orderDTO->billingLastName,
                'company' => $orderDTO->billingCompany,
                'address_1' => $orderDTO->billingAddressLine1,
                'address_2' => $orderDTO->billingAddressLine2,
                'city' => $orderDTO->billingCity,
                'state' => $orderDTO->billingState,
                'postcode' => $orderDTO->billingPostalCode,
                'country' => $orderDTO->billingCountry,
                'email' => $orderDTO->billingEmail,
                'phone' => $orderDTO->billingPhoneNumber,
            ],
            'shipping' => [
                'first_name' => $orderDTO->shippingFirstName,
                'last_name' => $orderDTO->shippingLastName,
                'company' => $orderDTO->shippingCompany ?? '',
                'address_1' => $orderDTO->shippingAddressLine1,
                'address_2' => $orderDTO->shippingAddressLine2,
                'city' => $orderDTO->shippingCity,
                'state' => $orderDTO->shippingState,
                'postcode' => $orderDTO->shippingPostalCode,
                'country' => $orderDTO->shippingCountry,
//                'email' => $orderDTO->shippingEmail,
//                'phone' => $orderDTO->shippingPhoneNumber,
            ],
            'meta_data' => $orderDTO->metaData,
            // products added to an order
            'line_items' => $orderDTO->uploads->map(fn ($uploadDTO) => [
                'product_id' => 3228,
                'quantity' => $uploadDTO->quantity,
                'subtotal' => (string) $uploadDTO->subtotal,
                'subtotal_tax' => (string) $uploadDTO->subtotalTax,
                'total' => (string) $uploadDTO->total,
                'total_tax' => (string) $uploadDTO->totalTax,
                'meta_data' => $uploadDTO->metaData,
            ])->toArray(),
//            'shipping_lines' => [
//                [
//                    'method_title' => 'Rate',
//                    'method_id' => 'flat_rate',
//                    'total' => $orderDTO->shippingFee,
//                    'total_tax' => $orderDTO->shippingFeeTax,
//                ],
//            ],
        ];
//        dd($data);

        return Order::create($data);
    }

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
