<?php

namespace App\OpenApi\Controllers;

use OpenApi\Annotations as OA;

class CustomersApiController
{
    /**
     * @OA\Get(
     *      path="/customers/{customer}",
     *      operationId="getCustomer",
     *      tags={"Customers"},
     *      summary="Get customer by ID",
     *      description="Returns a single customer",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="customer",
     *          description="Customer ID",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Customer")
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function show() {}

    /**
     * @OA\Post(
     *      path="/customers/{customerId}/token",
     *      operationId="issueCustomerApiToken",
     *      tags={"Customers"},
     *      summary="Issue a customer-scoped API token",
     *      description="Issues a short-lived API token scoped to a single customer, intended for the WordPress backend to request server-side and embed in that customer's own pages (e.g. 'My account > Integrations') instead of the shared backend token. The token can only be used to act on behalf of this one customer.",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="customerId",
     *          description="WordPress Customer ID",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="token", type="string", example="1|AbCdEf123...", description="Bearer token scoped to this customer"),
     *              @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, description="When the token expires")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function issueApiToken() {}
}
