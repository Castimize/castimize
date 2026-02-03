<?php

namespace App\OpenApi\Controllers;

use OpenApi\Annotations as OA;

class OrdersApiController
{
    /**
     * @OA\Get(
     *      path="/orders/{order_number}",
     *      operationId="getOrderByOrderNumber",
     *      tags={"Orders"},
     *      summary="Get order by order number",
     *      description="Returns a single order",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="order_number",
     *          description="Order number",
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
     *          @OA\JsonContent(ref="#/components/schemas/Order")
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
     *      path="/orders/calculate-expected-delivery-date",
     *      operationId="calculateExpectedDeliveryDate",
     *      tags={"Orders"},
     *      summary="Calculate expected delivery date",
     *      description="Calculates the expected delivery date based on uploads and destination country",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"country", "uploads"},
     *
     *              @OA\Property(property="country", type="string", example="NL", description="ISO 3166-1 alpha-2 country code"),
     *              @OA\Property(
     *                  property="uploads",
     *                  type="array",
     *                  description="Array of upload items",
     *
     *                  @OA\Items(
     *                      type="object",
     *
     *                      @OA\Property(property="material_id", type="integer", example=1),
     *                      @OA\Property(property="quantity", type="integer", example=2)
     *                  )
     *              )
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="expected_delivery_date", type="string", format="date", example="2024-01-22")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function calculateExpectedDeliveryDate() {}
}
