<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ModelResource;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ModelsApiController extends ApiController
{
    /**
     * @param int $customerId
     * @return AnonymousResourceCollection
     */
    public function showModelsWpCustomer(int $customerId): AnonymousResourceCollection
    {
        $customer = Customer::with('models.material')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        return ModelResource::collection($customer->models->keyBy->id);
    }

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
