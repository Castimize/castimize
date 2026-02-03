<?php

namespace App\Virtual\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Customer",
 *     description="Customer model",
 *
 *     @OA\Xml(
 *         name="Customer"
 *     )
 * )
 */
class Customer
{
    /**
     * @OA\Property(
     *     title="wp_id",
     *     description="WordPress customer ID",
     *     format="int64",
     *     example=123
     * )
     *
     * @var int
     */
    private $wp_id;

    /**
     * @OA\Property(
     *     title="email",
     *     description="Customer email address",
     *     format="email",
     *     example="customer@example.com"
     * )
     *
     * @var string
     */
    private $email;

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
     *     title="username",
     *     description="Customer username",
     *     example="johndoe"
     * )
     *
     * @var string
     */
    private $username;

    /**
     * @OA\Property(
     *     title="last_order",
     *     description="Last order information",
     *     type="object",
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="wp_id", type="integer", example=456),
     *     @OA\Property(property="created_at", type="string", format="datetime", example="2024-01-15 10:30:00")
     * )
     *
     * @var object
     */
    private $last_order;

    /**
     * @OA\Property(
     *     title="order_count",
     *     description="Total number of orders",
     *     example=5
     * )
     *
     * @var int
     */
    private $order_count;

    /**
     * @OA\Property(
     *     title="avatar",
     *     description="Customer avatar URL",
     *     example="https://example.com/avatars/user.jpg"
     * )
     *
     * @var string|null
     */
    private $avatar;

    /**
     * @OA\Property(
     *     title="date_created",
     *     description="Date created",
     *     format="datetime",
     *     type="string",
     *     example="2024-01-01 12:00:00"
     * )
     *
     * @var string
     */
    private $date_created;

    /**
     * @OA\Property(
     *     title="date_modified",
     *     description="Date modified",
     *     format="datetime",
     *     type="string",
     *     example="2024-01-15 14:30:00"
     * )
     *
     * @var string
     */
    private $date_modified;

    /**
     * @OA\Property(
     *     title="billing",
     *     description="Billing address",
     *     ref="#/components/schemas/Address"
     * )
     *
     * @var object
     */
    private $billing;

    /**
     * @OA\Property(
     *     title="shipping",
     *     description="Shipping address",
     *     ref="#/components/schemas/Address"
     * )
     *
     * @var object
     */
    private $shipping;
}
