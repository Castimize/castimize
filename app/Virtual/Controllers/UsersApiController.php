<?php

namespace App\Virtual\Controllers;

use OpenApi\Annotations as OA;

class UsersApiController
{
    /**
     * @OA\Get(
     *      path="/user",
     *      operationId="show",
     *      tags={"Users"},
     *      summary="Get own user information",
     *      description="Returns own user data",
     *      security={{"sanctum":{}}},
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(ref="#/components/schemas/User")
     *       ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      )
     * )
     */
    public function show()
    {
    }
}
