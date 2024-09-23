<?php

namespace App\Services\Admin;

use App\Models\Customer;
use App\Models\Order;

class OrdersService
{
    /**
     * Store a order completely from API request
     * @param $request
     * @return Order
     */
    public function storeOrderFromApi($request): Order
    {
        $customer = Customer::where('wp_id', $request->wp_customer_id)->first();

        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->billing_address_line1, $matchBilling);
        preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $request->shipping_address_line1, $matchShipping);

        $order = Order::create([
            'wp_id' => $request->wp_id,
            'customer_id' => $customer?->id,
            'order_number' => $request->order_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'billing_first_name' => $request->billing_first_name,
            'billing_last_name' => $request->billing_last_name,
            'billing_phone_number' => $request->billing_phone_number,
            'billing_address_line1' => $matchBilling[1] ?? $request->billing_address_line1,
            'billing_address_line2' => $request->billing_address_line2,
            'billing_house_number' => $matchBilling[2] ?? null,
            'billing_postal_code' => $request->billing_postal_code,
            'billing_city' => $request->billing_city,
            'billing_country' => $request->billing_country,
            'shipping_first_name' => $request->shipping_first_name,
            'shipping_last_name' => $request->shipping_last_name,
            'shipping_phone_number' => $request->shipping_phone_number,
            'shipping_address_line1' => $matchShipping[1] ?? $request->shipping_address_line1,
            'shipping_address_line2' => $request->shipping_address_line2,
            'shipping_house_number' => $matchShipping[2] ?? null,
            'shipping_postal_code' => $request->shipping_postal_code,
            'shipping_city' => $request->shipping_city,
            'shipping_country' => $request->shipping_country,
            'service_fee' => $request->service_fee,
            'service_fee_tax' => $request->service_fee_tax,
            'shipping_fee' => $request->shipping_fee,
            'shipping_fee_tax' => $request->shipping_fee_tax,
            'discount_fee' => $request->discount_fee,
            'discount_fee_tax' => $request->discount_fee_tax,
            'total' => $request->total,
            'total_tax' => $request->total_tax,
            'production_cost' => $request->production_cost,
            'production_cost_tax' => $request->production_cost_tax,
            'prices_include_tax' => $request->prices_include_tax,
            'payment_method' => $request->payment_method,
            'payment_issuer' => $request->payment_issuer,
            'customer_ip_address' => $request->customer_ip_address,
            'customer_user_agent' => $request->customer_user_agent,
            'comments' => $request->comments,
            'promo_code' => $request->promo_code,
            'created_at' => $request->date_created,
            'updated_at' => $request->date_modified,
        ]);

        return $order;
    }
}
