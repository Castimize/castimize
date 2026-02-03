<?php

namespace App\Virtual\Controllers;

use OpenApi\Annotations as OA;

class PaymentsApiController
{
    /**
     * @OA\Get(
     *      path="/customers/{customerId}/payments/create-setup-intent",
     *      operationId="createSetupIntent",
     *      tags={"Payments"},
     *      summary="Create Stripe setup intent",
     *      description="Creates a Stripe setup intent for setting up a payment method",
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
     *              @OA\Property(property="client_secret", type="string", example="seti_1ABC123_secret_XYZ789", description="Stripe client secret for setup intent")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Customer not found"
     *      )
     * )
     */
    public function createSetupIntent() {}

    /**
     * @OA\Post(
     *      path="/customers/{customerId}/payments/attach-payment-method",
     *      operationId="attachPaymentMethod",
     *      tags={"Payments"},
     *      summary="Attach payment method to customer",
     *      description="Attaches a Stripe payment method to a customer",
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
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"payment_method"},
     *
     *              @OA\Property(property="payment_method", type="string", example="pm_1ABC123XYZ", description="Stripe payment method ID")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=204,
     *          description="Payment method attached successfully"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Unable to attach payment method"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Customer not found"
     *      )
     * )
     */
    public function attachPaymentMethod() {}

    /**
     * @OA\Post(
     *      path="/customers/{customerId}/payments/cancel-mandate",
     *      operationId="cancelMandate",
     *      tags={"Payments"},
     *      summary="Cancel payment mandate",
     *      description="Cancels the Stripe payment mandate for a customer and deactivates their shop owner and all shops",
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
     *          response=204,
     *          description="Mandate cancelled successfully"
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Unable to cancel mandate"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Customer not found"
     *      )
     * )
     */
    public function cancelMandate() {}
}
