<?php

namespace App\Virtual\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="AddressValidation",
 *     description="Address validation response model",
 *
 *     @OA\Xml(
 *         name="AddressValidation"
 *     )
 * )
 */
class AddressValidation
{
    /**
     * @OA\Property(
     *     title="valid",
     *     description="Whether the address is valid",
     *     example=true
     * )
     *
     * @var bool
     */
    private $valid;

    /**
     * @OA\Property(
     *     title="address",
     *     description="Validated/corrected address",
     *     type="object",
     *     @OA\Property(property="name", type="string", example="John Doe"),
     *     @OA\Property(property="company", type="string", example="Acme Inc."),
     *     @OA\Property(property="street1", type="string", example="123 Main Street"),
     *     @OA\Property(property="street2", type="string", example="Suite 100"),
     *     @OA\Property(property="city", type="string", example="Amsterdam"),
     *     @OA\Property(property="state", type="string", example="NH"),
     *     @OA\Property(property="zip", type="string", example="1012 AB"),
     *     @OA\Property(property="country", type="string", example="NL")
     * )
     *
     * @var object
     */
    private $address;

    /**
     * @OA\Property(
     *     title="address_changed",
     *     description="Whether the address was modified during validation",
     *     example=0
     * )
     *
     * @var int
     */
    private $address_changed;

    /**
     * @OA\Property(
     *     title="messages",
     *     description="Validation messages or warnings",
     *     type="array",
     *
     *     @OA\Items(type="string", example="Address verified")
     * )
     *
     * @var array
     */
    private $messages;
}
