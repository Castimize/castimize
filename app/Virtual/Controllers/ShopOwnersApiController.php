<?php

namespace App\Virtual\Controllers;

use OpenApi\Annotations as OA;

class ShopOwnersApiController
{
    /**
     * @OA\Get(
     *      path="/customers/{customerId}/shop-owner",
     *      operationId="getShopOwner",
     *      tags={"ShopOwners"},
     *      summary="Get shop owner for a customer",
     *      description="Returns the shop owner information for a customer",
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
     *          @OA\JsonContent(ref="#/components/schemas/ShopOwner")
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
     *          description="Customer not found"
     *      )
     * )
     */
    public function show() {}

    /**
     * @OA\Get(
     *      path="/customers/{customerId}/shop-owner/{shop}",
     *      operationId="getShop",
     *      tags={"ShopOwners"},
     *      summary="Get a specific shop for a customer",
     *      description="Returns a specific shop's information for a customer's shop owner",
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
     *      @OA\Parameter(
     *          name="shop",
     *          description="Shop type (e.g., 'etsy')",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Shop")
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
     *          description="Not found"
     *      )
     * )
     */
    public function showShop() {}

    /**
     * @OA\Post(
     *      path="/customers/{customerId}/shop-owner",
     *      operationId="createShopOwner",
     *      tags={"ShopOwners"},
     *      summary="Create a shop owner for a customer",
     *      description="Creates a new shop owner record for a customer",
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
     *          required=false,
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="billing_eu_vat_number", type="string", example="NL123456789B01", description="EU VAT number")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ShopOwner")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad request - shop owner already exists"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function store() {}

    /**
     * @OA\Put(
     *      path="/customers/{customerId}/shop-owner",
     *      operationId="updateShopOwner",
     *      tags={"ShopOwners"},
     *      summary="Update a shop owner for a customer",
     *      description="Updates shop owner information and optionally adds a new shop",
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
     *          required=false,
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="billing_eu_vat_number", type="string", example="NL123456789B01", description="EU VAT number"),
     *              @OA\Property(property="shop", type="string", example="etsy", description="Shop type to add")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ShopOwner")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad request - shop owner doesn't exist"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function update() {}

    /**
     * @OA\Put(
     *      path="/customers/{customerId}/shop-owner/update-active",
     *      operationId="updateShopOwnerActive",
     *      tags={"ShopOwners"},
     *      summary="Toggle shop owner active status",
     *      description="Updates the active status for a shop owner and all their shops",
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
     *              required={"active"},
     *
     *              @OA\Property(property="active", type="string", example="1", description="Active status ('1' for active, '0' for inactive)")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/ShopOwner")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad request - shop owner doesn't exist"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function updateActive() {}

    /**
     * @OA\Post(
     *      path="/customers/{customerId}/shop-owner/{shop}/update-active",
     *      operationId="updateShopActive",
     *      tags={"ShopOwners"},
     *      summary="Toggle shop active status",
     *      description="Updates the active status for a specific shop. Requires an active payment mandate.",
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
     *      @OA\Parameter(
     *          name="shop",
     *          description="Shop type (e.g., 'etsy')",
     *          required=true,
     *          in="path",
     *
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"active"},
     *
     *              @OA\Property(property="active", type="string", example="1", description="Active status ('1' for active, '0' for inactive)")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Shop")
     *      ),
     *
     *      @OA\Response(
     *          response=400,
     *          description="Bad request - shop doesn't exist or no mandate found"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Shop not found"
     *      )
     * )
     */
    public function updateActiveShop() {}
}
