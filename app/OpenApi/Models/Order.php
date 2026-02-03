<?php

namespace App\OpenApi\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Order",
 *     description="Order model",
 *
 *     @OA\Xml(
 *         name="Order"
 *     )
 * )
 */
class Order
{
    /**
     * @OA\Property(
     *     title="customer_id",
     *     description="Customer ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $customer_id;

    /**
     * @OA\Property(
     *     title="country_id",
     *     description="Country ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $country_id;

    /**
     * @OA\Property(
     *     title="customer_shipment_id",
     *     description="Customer shipment ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int|null
     */
    private $customer_shipment_id;

    /**
     * @OA\Property(
     *     title="currency_id",
     *     description="Currency ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $currency_id;

    /**
     * @OA\Property(
     *     title="wp_id",
     *     description="WordPress order ID",
     *     format="int64",
     *     example=12345
     * )
     *
     * @var int
     */
    private $wp_id;

    /**
     * @OA\Property(
     *     title="order_number",
     *     description="Order number",
     *     example="ORD-2024-001"
     * )
     *
     * @var string
     */
    private $order_number;

    /**
     * @OA\Property(
     *     title="first_name",
     *     description="Customer first name",
     *     example="John"
     * )
     *
     * @var string
     */
    private $first_name;

    /**
     * @OA\Property(
     *     title="last_name",
     *     description="Customer last name",
     *     example="Doe"
     * )
     *
     * @var string
     */
    private $last_name;

    /**
     * @OA\Property(
     *     title="email",
     *     description="Customer email",
     *     format="email",
     *     example="john@example.com"
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *     title="billing",
     *     description="Billing address information",
     *     type="object",
     *     @OA\Property(property="first_name", type="string", example="John"),
     *     @OA\Property(property="last_name", type="string", example="Doe"),
     *     @OA\Property(property="phone_number", type="string", example="+31612345678"),
     *     @OA\Property(property="address_line1", type="string", example="123 Main Street"),
     *     @OA\Property(property="address_line2", type="string", example="Suite 100"),
     *     @OA\Property(property="postal_code", type="string", example="1012 AB"),
     *     @OA\Property(property="city", type="string", example="Amsterdam"),
     *     @OA\Property(property="country", type="string", example="NL")
     * )
     *
     * @var object
     */
    private $billing;

    /**
     * @OA\Property(
     *     title="shipping",
     *     description="Shipping address information",
     *     type="object",
     *     @OA\Property(property="first_name", type="string", example="John"),
     *     @OA\Property(property="last_name", type="string", example="Doe"),
     *     @OA\Property(property="phone_number", type="string", example="+31612345678"),
     *     @OA\Property(property="address_line1", type="string", example="123 Main Street"),
     *     @OA\Property(property="address_line2", type="string", example="Suite 100"),
     *     @OA\Property(property="postal_code", type="string", example="1012 AB"),
     *     @OA\Property(property="city", type="string", example="Amsterdam"),
     *     @OA\Property(property="country", type="string", example="NL")
     * )
     *
     * @var object
     */
    private $shipping;

    /**
     * @OA\Property(
     *     title="order_product_value",
     *     description="Order product value",
     *     format="float",
     *     example=99.99
     * )
     *
     * @var float
     */
    private $order_product_value;

    /**
     * @OA\Property(
     *     title="service_id",
     *     description="Service ID",
     *     example="standard"
     * )
     *
     * @var string|null
     */
    private $service_id;

    /**
     * @OA\Property(
     *     title="currency_code",
     *     description="Currency code",
     *     example="EUR"
     * )
     *
     * @var string
     */
    private $currency_code;

    /**
     * @OA\Property(
     *     title="service_fee",
     *     description="Service fee",
     *     format="float",
     *     example=5.00
     * )
     *
     * @var float
     */
    private $service_fee;

    /**
     * @OA\Property(
     *     title="service_fee_tax",
     *     description="Service fee tax",
     *     format="float",
     *     example=1.05
     * )
     *
     * @var float
     */
    private $service_fee_tax;

    /**
     * @OA\Property(
     *     title="shipping_fee",
     *     description="Shipping fee",
     *     format="float",
     *     example=10.00
     * )
     *
     * @var float
     */
    private $shipping_fee;

    /**
     * @OA\Property(
     *     title="shipping_fee_tax",
     *     description="Shipping fee tax",
     *     format="float",
     *     example=2.10
     * )
     *
     * @var float
     */
    private $shipping_fee_tax;

    /**
     * @OA\Property(
     *     title="discount_fee",
     *     description="Discount amount",
     *     format="float",
     *     example=0.00
     * )
     *
     * @var float
     */
    private $discount_fee;

    /**
     * @OA\Property(
     *     title="discount_fee_tax",
     *     description="Discount tax",
     *     format="float",
     *     example=0.00
     * )
     *
     * @var float
     */
    private $discount_fee_tax;

    /**
     * @OA\Property(
     *     title="total",
     *     description="Order total",
     *     format="float",
     *     example=115.09
     * )
     *
     * @var float
     */
    private $total;

    /**
     * @OA\Property(
     *     title="total_tax",
     *     description="Total tax",
     *     format="float",
     *     example=18.15
     * )
     *
     * @var float
     */
    private $total_tax;

    /**
     * @OA\Property(
     *     title="total_refund",
     *     description="Total refund amount",
     *     format="float",
     *     example=0.00
     * )
     *
     * @var float
     */
    private $total_refund;

    /**
     * @OA\Property(
     *     title="total_refund_tax",
     *     description="Total refund tax",
     *     format="float",
     *     example=0.00
     * )
     *
     * @var float
     */
    private $total_refund_tax;

    /**
     * @OA\Property(
     *     title="production_cost",
     *     description="Production cost",
     *     format="float",
     *     example=50.00
     * )
     *
     * @var float
     */
    private $production_cost;

    /**
     * @OA\Property(
     *     title="production_cost_tax",
     *     description="Production cost tax",
     *     format="float",
     *     example=10.50
     * )
     *
     * @var float
     */
    private $production_cost_tax;

    /**
     * @OA\Property(
     *     title="order_parts",
     *     description="Number of order parts",
     *     example=3
     * )
     *
     * @var int
     */
    private $order_parts;

    /**
     * @OA\Property(
     *     title="payment_method",
     *     description="Payment method",
     *     example="stripe"
     * )
     *
     * @var string
     */
    private $payment_method;

    /**
     * @OA\Property(
     *     title="payment_issuer",
     *     description="Payment issuer",
     *     example="visa"
     * )
     *
     * @var string|null
     */
    private $payment_issuer;

    /**
     * @OA\Property(
     *     title="comments",
     *     description="Order comments",
     *     example="Please handle with care"
     * )
     *
     * @var string|null
     */
    private $comments;

    /**
     * @OA\Property(
     *     title="promo_code",
     *     description="Promo code used",
     *     example="SAVE10"
     * )
     *
     * @var string|null
     */
    private $promo_code;

    /**
     * @OA\Property(
     *     title="fast_delivery_lead_time",
     *     description="Fast delivery lead time in days",
     *     example=3
     * )
     *
     * @var int|null
     */
    private $fast_delivery_lead_time;

    /**
     * @OA\Property(
     *     title="is_paid",
     *     description="Whether the order is paid",
     *     example=true
     * )
     *
     * @var bool
     */
    private $is_paid;

    /**
     * @OA\Property(
     *     title="paid_at",
     *     description="Payment timestamp",
     *     format="datetime",
     *     type="string",
     *     example="2024-01-15 10:30:00"
     * )
     *
     * @var string|null
     */
    private $paid_at;

    /**
     * @OA\Property(
     *     title="order_customer_lead_time",
     *     description="Customer lead time in days",
     *     example=7
     * )
     *
     * @var int
     */
    private $order_customer_lead_time;

    /**
     * @OA\Property(
     *     title="arrived_at",
     *     description="Arrival timestamp",
     *     format="datetime",
     *     type="string",
     *     example="2024-01-22 14:00:00"
     * )
     *
     * @var string|null
     */
    private $arrived_at;

    /**
     * @OA\Property(
     *     title="line_items",
     *     description="Order line items (uploads)",
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/Upload")
     * )
     *
     * @var array
     */
    private $line_items;
}
