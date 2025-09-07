<?php

namespace App\Virtual;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *      version="0.0.1",
 *      title="Castimize OpenApi Demo Documentation",
 *      description="Swagger OpenApi documentation for Castimize",
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
 *     name="Projects",
 *     description="API Endpoints of Projects"
 * )
 */
class Api {}
