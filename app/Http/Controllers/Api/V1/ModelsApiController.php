<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ModelsApiController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeFromUpload(Request $request): JsonResponse
    {
        Log::info(print_r($request->all(), true));

        return response()->json($request->toArray());
    }
}
