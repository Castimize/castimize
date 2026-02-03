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
}
