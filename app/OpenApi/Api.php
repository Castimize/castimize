<?php

namespace App\OpenApi;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="1.0.0",
 *      title="Castimize API Documentation",
 *      description="OpenAPI documentation for the Castimize public API endpoints",
 *
 *      @OA\Contact(
 *          email="matthbon@hotmail.com"
 *      ),
 *
 *      @OA\License(
 *          name="Apache 2.0",
 *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *      )
 * )
 *
 * @OA\Server(
 *      url=L5_SWAGGER_CONST_HOST,
 *      description="API Server"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints for User management"
 * )
 * @OA\Tag(
 *     name="Customers",
 *     description="API Endpoints for Customer management"
 * )
 * @OA\Tag(
 *     name="ShopOwners",
 *     description="API Endpoints for Shop Owner management"
 * )
 * @OA\Tag(
 *     name="Orders",
 *     description="API Endpoints for Order management"
 * )
 * @OA\Tag(
 *     name="Materials",
 *     description="API Endpoints for Material management"
 * )
 * @OA\Tag(
 *     name="Models",
 *     description="API Endpoints for 3D Model management"
 * )
 * @OA\Tag(
 *     name="Payments",
 *     description="API Endpoints for Payment management (Stripe)"
 * )
 * @OA\Tag(
 *     name="Prices",
 *     description="API Endpoints for Price calculation"
 * )
 * @OA\Tag(
 *     name="Address",
 *     description="API Endpoints for Address validation"
 * )
 */
class Api {}
