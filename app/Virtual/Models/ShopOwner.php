<?php

namespace App\Virtual\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="ShopOwner",
 *     description="Shop Owner model",
 *
 *     @OA\Xml(
 *         name="ShopOwner"
 *     )
 * )
 */
class ShopOwner
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="Shop owner ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int|null
     */
    private $id;

    /**
     * @OA\Property(
     *     title="is_shop_owner",
     *     description="Whether the customer is a shop owner",
     *     example=true
     * )
     *
     * @var bool
     */
    private $is_shop_owner;

    /**
     * @OA\Property(
     *     title="active",
     *     description="Whether the shop owner is active",
     *     example=true
     * )
     *
     * @var bool
     */
    private $active;

    /**
     * @OA\Property(
     *     title="vat_number",
     *     description="VAT number",
     *     example="NL123456789B01"
     * )
     *
     * @var string|null
     */
    private $vat_number;

    /**
     * @OA\Property(
     *     title="stripe_id",
     *     description="Stripe customer ID",
     *     example="cus_ABC123"
     * )
     *
     * @var string|null
     */
    private $stripe_id;

    /**
     * @OA\Property(
     *     title="payment_method",
     *     description="Payment method type",
     *     example="sepa_debit"
     * )
     *
     * @var string|null
     */
    private $payment_method;

    /**
     * @OA\Property(
     *     title="payment_method_chargable",
     *     description="Whether the payment method is chargeable",
     *     example=true
     * )
     *
     * @var bool
     */
    private $payment_method_chargable;

    /**
     * @OA\Property(
     *     title="payment_method_accepted_at",
     *     description="When the payment method was accepted",
     *     format="datetime",
     *     type="string",
     *     example="2024-01-15 10:30:00"
     * )
     *
     * @var string|null
     */
    private $payment_method_accepted_at;

    /**
     * @OA\Property(
     *     title="mandate",
     *     description="Mandate information",
     *     type="object",
     *     @OA\Property(property="id", type="string", example="mandate_ABC123"),
     *     @OA\Property(property="accepted_at", type="string", format="datetime", example="2024-01-15 10:30:00"),
     *     @OA\Property(property="payment_method", type="string", example="sepa_debit")
     * )
     *
     * @var object
     */
    private $mandate;

    /**
     * @OA\Property(
     *     title="shops",
     *     description="Associated shops keyed by shop name",
     *     type="object",
     *     additionalProperties={"$ref": "#/components/schemas/Shop"}
     * )
     *
     * @var object
     */
    private $shops;

    /**
     * @OA\Property(
     *     title="shops_list",
     *     description="Available shop types list",
     *     type="array",
     *
     *     @OA\Items(
     *         type="object",
     *
     *         @OA\Property(property="value", type="string", example="etsy"),
     *         @OA\Property(property="label", type="string", example="Etsy")
     *     )
     * )
     *
     * @var array
     */
    private $shops_list;
}
