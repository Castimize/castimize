<?php

namespace App\Virtual\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Shop",
 *     description="Shop model",
 *
 *     @OA\Xml(
 *         name="Shop"
 *     )
 * )
 */
class Shop
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="Shop ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="shop",
     *     description="Shop type name",
     *     example="etsy"
     * )
     *
     * @var string
     */
    private $shop;

    /**
     * @OA\Property(
     *     title="active",
     *     description="Whether the shop is active",
     *     example=true
     * )
     *
     * @var bool
     */
    private $active;

    /**
     * @OA\Property(
     *     title="shop_id",
     *     description="External shop ID (e.g., Etsy shop ID)",
     *     example="12345678"
     * )
     *
     * @var string|null
     */
    private $shop_id;

    /**
     * @OA\Property(
     *     title="shop_currency",
     *     description="Shop currency code",
     *     example="EUR"
     * )
     *
     * @var string|null
     */
    private $shop_currency;

    /**
     * @OA\Property(
     *     title="auth_url",
     *     description="Authorization URL for OAuth (if required)",
     *     example="https://www.etsy.com/oauth/connect?..."
     * )
     *
     * @var string|null
     */
    private $auth_url;
}
