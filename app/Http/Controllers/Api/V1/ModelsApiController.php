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
use Symfony\Component\HttpFoundation\Response;

class ModelsApiController extends ApiController
{
    public function __construct(private ModelsService $modelsService)
    {
    }

    public function show(int $customerId, Model $model): ModelResource
    {
        abort_if(Gate::denies('viewModel'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (!$model || (int)$model->customer->wp_id !== $customerId) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

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

        $models = [];
        foreach ($customer->models as $model) {
            $key = sprintf('%s-%s-%s-%s-%s-%s-%s-%s-%s',
                $model->model_name,
                $model->name,
                $model->material_id,
                $model->model_volume_cc,
                $model->model_surface_area_cm2,
                $model->model_box_volume,
                $model->model_x_length,
                $model->model_y_length,
                $model->model_z_length
            );
            if (!array_key_exists($key, $models)) {
                $models[$key] = $model;
            }
        }
        $models = collect($models);

        $response = ModelResource::collection($models->keyBy->id);
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

    public function getCustomModelNames(int $customerId, Request $request): JsonResponse
    {
        $customer = Customer::with('models.material')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $newUploads = [];
        foreach (json_decode($request->uploads, true, 512, JSON_THROW_ON_ERROR) as $itemKey => $upload) {
            [$materialId, $materialName] = explode('. ', $upload['3dp_options']['material_name']);
            $model = Model::where('name', $upload['3dp_options']['filename'])
                ->where('file_name', $upload['3dp_options']['model_name'])
                ->where('material_id', $materialId)
                ->where('model_volume_cc', $upload['3dp_options']['model_stats_raw']['model']['material_volume'])
                ->where('model_surface_area_cm2', $upload['3dp_options']['model_stats_raw']['model']['surface_area'])
                ->where('model_box_volume', $upload['3dp_options']['model_stats_raw']['model']['box_volume'])
                ->where('model_x_length', $upload['3dp_options']['model_stats_raw']['model']['x_dim'])
                ->where('model_y_length', $upload['3dp_options']['model_stats_raw']['model']['y_dim'])
                ->where('model_z_length', $upload['3dp_options']['model_stats_raw']['model']['z_dim'])
                ->first();

            $newUploads[$itemKey] = $upload;
            $newUploads[$itemKey]['3dp_options']['model_name_original'] = $model->model_name;
        }

        return response()->json($newUploads);
    }

    public function store(int $customerId, Request $request): JsonResponse
    {
        $customer = Customer::where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $model = $this->modelsService->storeModelFromApi($request, $customer->id);

        $response = new ModelResource($model);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, int $customerId, Model $model): JsonResponse
    {
        if (!$model || (int)$model->customer->wp_id !== $customerId) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $model = $this->modelsService->updateModelFromApi($request, $model, $model->customer_id);

        $response = new ModelResource($model);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(int $customerId, Model $model): Response
    {
        if (!$model || (int)$model->customer->wp_id !== $customerId) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $model->delete();

        return response(null, Response::HTTP_NO_CONTENT);
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
