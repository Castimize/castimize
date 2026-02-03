<?php

namespace App\OpenApi\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Address",
 *     description="Address model",
 *
 *     @OA\Xml(
 *         name="Address"
 *     )
 * )
 */
class Address
{
    /**
     * @OA\Property(
     *     title="first_name",
     *     description="First name",
     *     example="John"
     * )
     *
     * @var string
     */
    private $first_name;

    /**
     * @OA\Property(
     *     title="last_name",
     *     description="Last name",
     *     example="Doe"
     * )
     *
     * @var string
     */
    private $last_name;

    /**
     * @OA\Property(
     *     title="company",
     *     description="Company name",
     *     example="Acme Inc."
     * )
     *
     * @var string|null
     */
    private $company;

    /**
     * @OA\Property(
     *     title="address_1",
     *     description="Address line 1",
     *     example="123 Main Street"
     * )
     *
     * @var string
     */
    private $address_1;

    /**
     * @OA\Property(
     *     title="address_2",
     *     description="Address line 2",
     *     example="Suite 100"
     * )
     *
     * @var string|null
     */
    private $address_2;

    /**
     * @OA\Property(
     *     title="city",
     *     description="City",
     *     example="Amsterdam"
     * )
     *
     * @var string
     */
    private $city;

    /**
     * @OA\Property(
     *     title="state",
     *     description="State/Province",
     *     example="NH"
     * )
     *
     * @var string|null
     */
    private $state;

    /**
     * @OA\Property(
     *     title="postcode",
     *     description="Postal/ZIP code",
     *     example="1012 AB"
     * )
     *
     * @var string
     */
    private $postcode;

    /**
     * @OA\Property(
     *     title="country",
     *     description="Country code (ISO 3166-1 alpha-2)",
     *     example="NL"
     * )
     *
     * @var string
     */
    private $country;

    /**
     * @OA\Property(
     *     title="email",
     *     description="Email address",
     *     format="email",
     *     example="john@example.com"
     * )
     *
     * @var string
     */
    private $email;

    /**
     * @OA\Property(
     *     title="phone",
     *     description="Phone number",
     *     example="+31612345678"
     * )
     *
     * @var string|null
     */
    private $phone;
}
