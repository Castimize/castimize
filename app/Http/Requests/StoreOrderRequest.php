<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        abort_if(Gate::denies('createOrder'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'wp_id' => 'nullable',
            'wp_customer_id' => 'nullable',
            'order_number' => 'nullable',
            'first_name' => 'nullable',
            'last_name' => 'nullable',
            'email' => 'nullable',
            'billing_first_name' => 'nullable',
            'billing_last_name' => 'nullable',
            'billing_phone_number' => 'nullable',
            'billing_address_line1' => 'nullable',
            'billing_address_line2' => 'nullable',
            'billing_postal_code' => 'nullable',
            'billing_city' => 'nullable',
            'billing_country' => 'nullable',
            'shipping_first_name' => 'nullable',
            'shipping_last_name' => 'nullable',
            'shipping_phone_number' => 'nullable',
            'shipping_address_line1' => 'nullable',
            'shipping_address_line2' => 'nullable',
            'shipping_postal_code' => 'nullable',
            'shipping_city' => 'nullable',
            'shipping_country' => 'nullable',
            'service_fee' => 'nullable',
            'service_fee_tax' => 'nullable',
            'shipping_fee' => 'nullable',
            'shipping_fee_tax' => 'nullable',
            'discount_fee' => 'nullable',
            'discount_fee_tax' => 'nullable',
            'total' => 'nullable',
            'total_tax' => 'nullable',
            'production_cost' => 'nullable',
            'production_cost_tax' => 'nullable',
            'prices_include_tax' => 'nullable',
            'payment_method' => 'nullable',
            'payment_issuer' => 'nullable',
            'customer_ip_address' => 'nullable',
            'customer_user_agent' => 'nullable',
            'comments' => 'nullable',
            'promo_code' => 'nullable',
            'date_created' => 'nullable',
            'date_modified' => 'nullable',
            'line_items' => 'nullable',
        ];
    }
}
