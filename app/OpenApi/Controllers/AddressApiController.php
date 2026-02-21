<?php

namespace App\OpenApi\Controllers;

use OpenApi\Annotations as OA;

class AddressApiController
{
    /**
     * @OA\Post(
     *      path="/address/validate",
     *      operationId="validateAddress",
     *      tags={"Address"},
     *      summary="Validate an address",
     *      description="Validates an address using the Shippo address validation service",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"name", "address_1", "city", "postal_code", "country", "email"},
     *
     *              @OA\Property(property="name", type="string", example="John Doe", description="Full name"),
     *              @OA\Property(property="company", type="string", nullable=true, example="Acme Inc.", description="Company name"),
     *              @OA\Property(property="address_1", type="string", example="123 Main Street", description="Address line 1"),
     *              @OA\Property(property="address_2", type="string", nullable=true, example="Suite 100", description="Address line 2"),
     *              @OA\Property(property="city", type="string", example="Amsterdam", description="City"),
     *              @OA\Property(property="state", type="string", nullable=true, example="NH", description="State/Province"),
     *              @OA\Property(property="postal_code", type="string", example="1012 AB", description="Postal/ZIP code"),
     *              @OA\Property(property="country", type="string", example="NL", description="ISO 3166-1 alpha-2 country code"),
     *              @OA\Property(property="email", type="string", format="email", example="john@example.com", description="Email address")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/AddressValidation")
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function validate() {}
}
