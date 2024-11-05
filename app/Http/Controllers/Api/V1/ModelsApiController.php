<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ModelResource;
use App\Models\Customer;
use App\Models\Model;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\ModelsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ModelsApiController extends ApiController
{
    public function __construct(private ModelsService $modelsService)
    {
    }

    /**
     * @param Model $model
     * @return ModelResource
     */
    public function show(Model $model): ModelResource
    {
        abort_if(Gate::denies('viewModel'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $response = new ModelResource($model);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }

    /**
     * @param int $customerId
     * @return AnonymousResourceCollection
     */
    public function showModelsWpCustomer(int $customerId): AnonymousResourceCollection
    {
        $customer = Customer::with('models.material')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $response = ModelResource::collection($customer->models->keyBy->id);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }

    public function storeModelWp(Request $request): JsonResponse
    {
        $model = $this->modelsService->storeModelFromApi($request);

        $response = new ModelResource($model);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeFromUpload(Request $request): JsonResponse
    {
        return response()->json($request->toArray());
    }
}
