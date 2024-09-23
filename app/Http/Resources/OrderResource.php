<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'customer_id' => $this->customer_id,
            'country_id' => $this->country_id,
            'customer_shipment_id' => $this->customer_shipment_id,
            'currency_id' => $this->currency_id,
            'wp_id' => $this->wp_id,
            'order_number' => $this->order_number,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'billing' => [
                'first_name' => $this->billing_first_name,
                'last_name' => $this->billing_last_name,
                'phone_number' => $this->billing_phone_number,
                'address_line1' => $this->billing_address_line1,
                'address_line2' => $this->billing_address_line2,
                'house_number' => $this->billing_house_number,
                'postal_code' => $this->billing_postal_code,
                'city' => $this->billing_city,
                'country' => $this->billing_country,
            ],
            'shipping' => [
                'first_name' => $this->shipping_first_name,
                'last_name' => $this->shipping_last_name,
                'phone_number' => $this->shipping_phone_number,
                'address_line1' => $this->shipping_address_line1,
                'address_line2' => $this->shipping_address_line2,
                'house_number' => $this->shipping_house_number,
                'postal_code' => $this->shipping_postal_code,
                'city' => $this->shipping_city,
                'country' => $this->shipping_country,
            ],
            'order_product_value' => $this->order_product_value,
            'service_id' => $this->service_id,
            'service_fee' => $this->service_fee,
            'service_fee_tax' => $this->service_fee_tax,
            'shipping_fee' => $this->shipping_fee,
            'shipping_fee_tax' => $this->shipping_fee_tax,
            'discount_fee' => $this->discount_fee,
            'discount_fee_tax' => $this->discount_fee_tax,
            'total' => $this->total,
            'total_tax' => $this->total_tax,
            'production_cost' => $this->production_cost,
            'production_cost_tax' => $this->production_cost_tax,
            'order_parts' => $this->order_parts,
            'payment_method' => $this->payment_method,
            'payment_issuer' => $this->payment_issuer,
            'payment_intent_id' => $this->payment_intent_id,
            'customer_ip_address' => $this->customer_ip_address,
            'customer_user_agent' => $this->customer_user_agent,
            'comments' => $this->comments,
            'promo_code' => $this->promo_code,
            'fast_delivery_lead_time' => $this->fast_delivery_lead_time,
            'is_paid' => $this->is_paid,
            'paid_at' => $this->paid_at,
            'order_customer_lead_time' => $this->order_customer_lead_time,
            'arrived_at' => $this->arrived_at,
            'line_items' => [],
        ];
    }
}
