<?php

namespace App\Virtual\Controllers;

use OpenApi\Annotations as OA;

class ModelsApiController
{
    /**
     * @OA\Get(
     *      path="/models/wp/{customerId}",
     *      operationId="getCustomerModels",
     *      tags={"Models"},
     *      summary="Get all models for a customer",
     *      description="Returns a list of unique 3D models for a customer identified by WordPress ID",
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
     *              type="array",
     *
     *              @OA\Items(ref="#/components/schemas/Model3D")
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
    public function showModelsWpCustomer() {}

    /**
     * @OA\Get(
     *      path="/models/wp/{customerId}/{model}",
     *      operationId="getCustomerModel",
     *      tags={"Models"},
     *      summary="Get a single model for a customer",
     *      description="Returns a specific 3D model for a customer",
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
     *          name="model",
     *          description="Model ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/Model3D")
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
    public function show() {}

    /**
     * @OA\Post(
     *      path="/models/wp/{customerId}",
     *      operationId="storeCustomerModel",
     *      tags={"Models"},
     *      summary="Create a new model for a customer",
     *      description="Creates a new 3D model for a customer",
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
     *              required={"name", "file_name", "model_volume_cc", "model_surface_area_cm2"},
     *
     *              @OA\Property(property="name", type="string", example="part_001.stl"),
     *              @OA\Property(property="model_name", type="string", example="Custom Part v1"),
     *              @OA\Property(property="file_name", type="string", example="wp-content/uploads/p3d/part_001.stl"),
     *              @OA\Property(property="model_volume_cc", type="number", format="float", example=12.5),
     *              @OA\Property(property="model_surface_area_cm2", type="number", format="float", example=85.5),
     *              @OA\Property(property="model_x_length", type="number", format="float", example=50.0),
     *              @OA\Property(property="model_y_length", type="number", format="float", example=30.0),
     *              @OA\Property(property="model_z_length", type="number", format="float", example=20.0),
     *              @OA\Property(property="model_box_volume", type="number", format="float", example=30000.0),
     *              @OA\Property(property="model_scale", type="number", format="float", example=1.0),
     *              @OA\Property(property="material_id", type="integer", example=1)
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Created",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Model3D")
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
    public function store() {}

    /**
     * @OA\Post(
     *      path="/models/wp/{customerId}/{model}",
     *      operationId="updateCustomerModel",
     *      tags={"Models"},
     *      summary="Update a model for a customer",
     *      description="Updates an existing 3D model for a customer",
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
     *          name="model",
     *          description="Model ID",
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
     *              @OA\Property(property="model_name", type="string", example="Custom Part v2"),
     *              @OA\Property(property="categories", type="array", @OA\Items(type="object", @OA\Property(property="category", type="string", example="Automotive")))
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=201,
     *          description="Updated",
     *
     *          @OA\JsonContent(ref="#/components/schemas/Model3D")
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      )
     * )
     */
    public function update() {}

    /**
     * @OA\Post(
     *      path="/models/wp/{customerId}/{model}/delete",
     *      operationId="deleteCustomerModel",
     *      tags={"Models"},
     *      summary="Delete a model for a customer",
     *      description="Deletes a 3D model for a customer",
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
     *          name="model",
     *          description="Model ID",
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
     *          description="Deleted"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      )
     * )
     */
    public function destroy() {}

    /**
     * @OA\Post(
     *      path="/models/wp/{customerId}/paginated",
     *      operationId="getCustomerModelsPaginated",
     *      tags={"Models"},
     *      summary="Get paginated models for a customer",
     *      description="Returns a paginated list of 3D models for a customer",
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
     *              @OA\Property(property="page", type="integer", example=1),
     *              @OA\Property(property="per_page", type="integer", example=10),
     *              @OA\Property(property="search", type="string", example="part")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="items", type="array", @OA\Items(ref="#/components/schemas/Model3D")),
     *              @OA\Property(property="total", type="integer", example=50),
     *              @OA\Property(property="filtered", type="integer", example=10)
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
    public function showModelsWpCustomerPaginated() {}

    /**
     * @OA\Post(
     *      path="/models/wp/{customerId}/get-custom-model-name",
     *      operationId="getCustomModelName",
     *      tags={"Models"},
     *      summary="Get custom model name",
     *      description="Returns the custom model name if it exists for the given upload",
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
     *              required={"upload"},
     *
     *              @OA\Property(property="upload", type="string", description="JSON encoded upload object")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *
     *              @OA\Property(property="model_name", type="string", nullable=true, example="Custom Part v1")
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
    public function getCustomModelName() {}

    /**
     * @OA\Post(
     *      path="/models/wp/{customerId}/get-custom-model-attributes",
     *      operationId="getCustomModelAttributes",
     *      tags={"Models"},
     *      summary="Get custom model attributes",
     *      description="Returns the custom model attributes (thumbnails, names) for uploads",
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
     *              required={"uploads"},
     *
     *              @OA\Property(property="uploads", type="string", description="JSON encoded uploads array")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *              description="Uploads with custom attributes added"
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
    public function getCustomModelAttributes() {}

    /**
     * @OA\Post(
     *      path="/models/store-from-upload",
     *      operationId="storeModelFromUpload",
     *      tags={"Models"},
     *      summary="Store model from upload",
     *      description="Creates a model directly from an upload request",
     *      security={{"sanctum":{}}},
     *
     *      @OA\RequestBody(
     *          required=true,
     *
     *          @OA\JsonContent(
     *              type="object",
     *              description="Upload data"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="object",
     *              description="Request data echoed back"
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function storeFromUpload() {}
}
