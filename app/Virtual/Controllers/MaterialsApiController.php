<?php

namespace App\Virtual\Controllers;

use OpenApi\Annotations as OA;

class MaterialsApiController
{
    /**
     * @OA\Get(
     *      path="/materials",
     *      operationId="getMaterials",
     *      tags={"Materials"},
     *      summary="Get all materials",
     *      description="Returns a list of all available materials",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *
     *          @OA\JsonContent(
     *              type="array",
     *
     *              @OA\Items(ref="#/components/schemas/Material")
     *          )
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      )
     * )
     */
    public function index() {}

    /**
     * @OA\Get(
     *      path="/materials/{material}",
     *      operationId="getMaterial",
     *      tags={"Materials"},
     *      summary="Get material by ID",
     *      description="Returns a single material",
     *      security={{"sanctum":{}}},
     *
     *      @OA\Parameter(
     *          name="material",
     *          description="Material ID",
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
     *          @OA\JsonContent(ref="#/components/schemas/Material")
     *      ),
     *
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found"
     *      )
     * )
     */
    public function show() {}
}
