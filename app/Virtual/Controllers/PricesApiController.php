<?php

namespace App\Virtual\Controllers;

use OpenApi\Annotations as OA;

class PricesApiController
{
    /**
     * @OA\Post(
     *      path="/prices/calculate",
     *      operationId="calculatePrice",
     *      tags={"Prices"},
     *      summary="Calculate price for a 3D model",
     *      description="Calculates the price for a 3D model based on material, dimensions, and volume",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"currency", "wp_id", "material_volume", "support_volume", "box_volume", "surface_area", "scale", "weight", "x_dim", "y_dim", "z_dim", "quantity", "original_file_name", "file_name"},
     *
     *              @OA\Property(property="currency", type="string", example="EUR", description="Currency code"),
     *              @OA\Property(property="wp_id", type="integer", example=123, description="WordPress material ID"),
     *              @OA\Property(property="printer_id", type="integer", nullable=true, example=null, description="Printer ID"),
     *              @OA\Property(property="coating_id", type="integer", nullable=true, example=null, description="Coating ID"),
     *              @OA\Property(property="material_volume", type="number", format="float", example=12.5, description="Material volume in cc"),
     *              @OA\Property(property="support_volume", type="number", format="float", example=2.5, description="Support material volume in cc"),
     *              @OA\Property(property="print_time", type="number", nullable=true, example=null, description="Estimated print time"),
     *              @OA\Property(property="box_volume", type="number", format="float", example=30000.0, description="Bounding box volume"),
     *              @OA\Property(property="surface_area", type="number", format="float", example=85.5, description="Surface area in cm2"),
     *              @OA\Property(property="scale", type="number", format="float", example=1.0, description="Scale factor"),
     *              @OA\Property(property="weight", type="number", format="float", example=25.0, description="Weight in grams"),
     *              @OA\Property(property="x_dim", type="number", format="float", example=50.0, description="X dimension in mm"),
     *              @OA\Property(property="y_dim", type="number", format="float", example=30.0, description="Y dimension in mm"),
     *              @OA\Property(property="z_dim", type="number", format="float", example=20.0, description="Z dimension in mm"),
     *              @OA\Property(property="polygons", type="integer", nullable=true, example=null, description="Number of polygons"),
     *              @OA\Property(property="quantity", type="integer", example=1, description="Quantity"),
     *              @OA\Property(property="original_file_name", type="string", example="part_001.stl", description="Original file name"),
     *              @OA\Property(property="file_name", type="string", example="wp-content/uploads/p3d/part_001.stl", description="Stored file name"),
     *              @OA\Property(property="thumb", type="string", nullable=true, example=null, description="Thumbnail URL"),
     *              @OA\Property(property="customer_id", type="integer", nullable=true, example=123, description="WordPress customer ID for model storage")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(ref="#/components/schemas/CalculatedPrice")
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="errors", type="string", example="Material not found")
     *          )
     *      )
     * )
     */
    public function calculatePrice() {}

    /**
     * @OA\Post(
     *      path="/prices/calculate/shipping",
     *      operationId="calculateShipping",
     *      tags={"Prices"},
     *      summary="Calculate shipping fee",
     *      description="Calculates the shipping fee based on destination country and upload volumes",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              required={"country", "uploads", "currency"},
     *
     *              @OA\Property(property="country", type="string", example="NL", description="ISO 3166-1 alpha-2 country code"),
     *              @OA\Property(property="currency", type="string", example="EUR", description="Currency code for conversion"),
     *              @OA\Property(
     *                  property="uploads",
     *                  type="array",
     *                  description="Array of upload items",
     *
     *                  @OA\Items(
     *                      type="object",
     *
     *                      @OA\Property(property="material_volume", type="number", format="float", example=12.5),
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
     *          @OA\JsonContent(ref="#/components/schemas/CalculatedShippingFee")
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="errors", type="string", example="Country not found")
     *          )
     *      )
     * )
     */
    public function calculateShipping() {}
}
