<?php

namespace App\Services\Woocommerce;

use App\DTO\Customer\CustomerDTO;
use App\DTO\Order\OrderDTO;
use App\DTO\Order\PaymentFeeDTO;
use App\DTO\Order\UploadDTO;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;
use Codexshaper\WooCommerce\Facades\Tax;

class WoocommerceApiService
{
    public function updateCustomerVatNumber(CustomerDTO $customerDTO)
    {
        $data = [
            'meta_data' => [
                [
                    'key' => 'billing_eu_vat_number',
                    'value' => $customerDTO->vatNumber,
                ],
            ],
        ];

        return Customer::update($customerDTO->wpId, $data);
    }


    public function createOrder(OrderDTO $orderDTO)
    {
        $data = [
            //'status' => $orderDTO->status,
            'customer_id' => $orderDTO->customerId,
            'currency' => $orderDTO->currencyCode,
            'prices_include_tax' => false,
//            'total' => $orderDTO->total->toString(),
//            'total_tax' => $orderDTO->totalTax->toString(),
//            'shipping_total' => $orderDTO->shippingFee->toString(),
//            'shipping_tax' => $orderDTO->shippingFeeTax->toString(),
            'set_paid' => true,
            'billing' => [
                'first_name' => $orderDTO->billingFirstName,
                'last_name' => $orderDTO->billingLastName,
                'company' => $orderDTO->billingCompany,
                'address_1' => $orderDTO->billingAddressLine1,
                'address_2' => $orderDTO->billingAddressLine2 ?? '',
                'city' => $orderDTO->billingCity,
                'state' => $orderDTO->billingState ?? '',
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
                'address_2' => $orderDTO->shippingAddressLine2 ?? '',
                'city' => $orderDTO->shippingCity,
                'state' => $orderDTO->shippingState ?? '',
                'postcode' => $orderDTO->shippingPostalCode,
                'country' => $orderDTO->shippingCountry,
            ],
            'meta_data' => $orderDTO->metaData,
            // products added to an order
            'line_items' => $orderDTO->uploads->map(fn (UploadDTO $uploadDTO) => [
                'product_id' => 3228,
//                'name' => '3D',
                'quantity' => $uploadDTO->quantity,
                'subtotal' => $uploadDTO->subtotal->toString(),
                'subtotal_tax' => $uploadDTO->subtotalTax?->toString(),
                'total' => $uploadDTO->total->toString(),
                'total_tax' => $uploadDTO->totalTax?->toString(),
                'meta_data' => $uploadDTO->metaData,
                'taxes' => $uploadDTO->totalTax && $uploadDTO->totalTax->toFloat() > 0.00 ? [
                    [
                        'rate_id' => 23,
                        'tax_total' => $uploadDTO->totalTax->toString(),
                    ],
                ] : [],
            ])->toArray(),
            'shipping_lines' => [
                [
                    'method_title' => 'Rate',
                    'method_id' => 'flat_rate',
                    'total' => $orderDTO->shippingFee?->toString(),
                    'total_tax' => $orderDTO->shippingFeeTax?->toString(),
                    'taxes' => $orderDTO->shippingFeeTax && $orderDTO->shippingFeeTax->toFloat() > 0.00 ? [
                        [
                            'rate_id' => 23,
                            'tax_total' => $orderDTO->shippingFeeTax->toString(),
                        ],
                    ] : [],
                ],
            ],
        ];

        if ($orderDTO->totalTax && $orderDTO->totalTax->toFloat() > 0.00) {
            $data['tax_lines'][] = [
                'rate_code' => 'NL-VAT-1',
                'rate_id' => 23,
                'label' => 'VAT',
                'compound' => false,
                'tax_total' => $orderDTO->totalTax->toString(),
                'shipping_tax_total' => $orderDTO->shippingFeeTax?->toString(),
                'rate_percent' => 21,
                'meta_data' => [
                    [
                        'key' => '_wcpdf_rate_percentage',
                        'value' => '21.0000',
                    ],
                ],
            ];
        }

        if ($orderDTO->paymentFees->count() > 0) {
            $data['fee_lines'] = [];
            /** @var PaymentFeeDTO $paymentFeeDTO */
            foreach ($orderDTO->paymentFees as $paymentFeeDTO) {
//                $total = $paymentFeeDTO->total;
//                if ($paymentFeeDTO->totalTax) {
//                    $total = $total->subtract($paymentFeeDTO->totalTax);
//                }
                $data['fee_lines'][] = [
                    'name' => $paymentFeeDTO->name,
                    'tax_class' => $paymentFeeDTO->taxClass,
                    'tax_status' => $paymentFeeDTO->taxStatus,
                    'total' => $paymentFeeDTO->total->toString(),
                    'total_tax' => $paymentFeeDTO->totalTax?->toString(),
                    'taxes' => $paymentFeeDTO->totalTax && $paymentFeeDTO->totalTax->toFloat() > 0.00 ? [
                        [
                            'rate_id' => 23,
                            'tax_total' => $paymentFeeDTO->totalTax->toString(),
                        ],
                    ] : [],
                    'meta_data' => $paymentFeeDTO->metaData,
                ];
            }
        }
//        $taxes = Tax::all();
//        dd($taxes);
//        dd($data);

        return Order::create($data);
    }

    public function updateOrder(OrderDTO $orderDTO)
    {
        $data = [
            'set_paid' => $orderDTO->isPaid,
            'meta_data' => $orderDTO->metaData,
        ];
        return Order::update($orderDTO->orderNumber, $data);
    }

    public function deleteOrder(int $wpOrderId)
    {
        $options = ['force' => true]; // Set force option true for delete permanently. Default value false

        return Order::delete($wpOrderId, $options);
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
