<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Model\ModelDTO;
use App\Http\Resources\ModelResource;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\ModelsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ModelsApiController extends ApiController
{
    public function __construct(
        private ModelsService $modelsService,
    ) {
    }

    public function show(int $customerId, Model $model): ModelResource
    {
        abort_if(Gate::denies('viewModel'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (! $model || (int) $model->customer?->wp_id !== $customerId) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $response = new ModelResource($model);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }

    public function showModelsWpCustomer(int $customerId): AnonymousResourceCollection
    {
        $customer = Customer::with('models.materials')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $models = [];
        foreach ($customer->models as $model) {
            $key = sprintf('%s-%s-%s-%s-%s-%s-%s-%s',
                $model->model_name,
                $model->name,
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

    public function showModelsWpCustomerPaginated(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $response = $this->modelsService->getModelsPaginated($request, $customer);

        LogRequestService::addResponse(request(), $response['items']);
        return response()->json([
            'items' => $response['items'],
            'total' => $response['total'],
            'filtered' => $response['filtered'] ?? $response['items']->count(),
        ]);
    }

    public function getCustomModelName(int $customerId, Request $request): JsonResponse
    {
        $customer = Customer::with('models.materials')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $upload = json_decode($request->upload, true, 512, JSON_THROW_ON_ERROR);
        [$materialId, $materialName] = array_pad(explode('. ', $upload['3dp_options']['material_name']), 2, null);

        $model = $customer->models->where('name', $upload['3dp_options']['filename'])
            ->where('file_name', 'wp-content/uploads/p3d/' . $upload['3dp_options']['model_name'])
            ->where('material_id', $materialId)
            ->where('model_volume_cc', $upload['3dp_options']['model_stats_raw']['model']['material_volume'])
            ->first();

        $modelName = $model ? $model->model_name : null;

        return response()->json(['model_name' => $modelName]);
    }

    public function getCustomModelAttributes(int $customerId, Request $request): JsonResponse
    {
        ini_set('precision', 53);
        $customer = Customer::with('models.materials')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $newUploads = [];
        foreach (json_decode($request->uploads, true, 512, JSON_THROW_ON_ERROR) as $itemKey => $upload) {
            if (array_key_exists('3dp_options', $upload)) {
                [$materialId, $materialName] = array_pad(explode('. ', $upload['3dp_options']['material_name']), 2, null);
                $material = Material::where('wp_id', ($upload['3dp_options']['material_id'] ?? $materialId))->first();
                $model = null;
                if ($material) {
                    $model = $customer->models->where('file_name', 'wp-content/uploads/p3d/' . str_replace('_resized', '', $upload['3dp_options']['model_name']))
                        ->where('material_id', $material->id)
                        ->where('model_scale', $upload['3dp_options']['scale'])
                        ->first();
                }

                $newUploads[$itemKey] = $upload;
                if ($model) {
                    if ($model->thumb_name) {
                        $newUploads[$itemKey]['3dp_options']['thumbnail'] = Storage::disk(env('FILESYSTEM_DISK'))->exists($model->thumb_name) ? sprintf('%s/%s', env('AWS_URL'), $model->thumb_name) : '/' . $model->thumb_name;
                    }
                    $newUploads[$itemKey]['3dp_options']['model_name_original'] = $model->model_name ?: $upload['3dp_options']['model_name_original'];
                }
            }
        }

        LogRequestService::addResponse($request, $newUploads);
        return response()->json($newUploads);
    }
    public function store(int $customerId, Request $request): JsonResponse
    {
        ini_set('precision', 17);
        $customer = Customer::where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $model = $this->modelsService->storeModelFromModelDTO(ModelDTO::fromWpRequest($request, $customer->id), $customer);

        $response = new ModelResource($model);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, int $customerId, Model $model): JsonResponse
    {
        ini_set('precision', 53);
        if (! $model || (int) $model->customer?->wp_id !== $customerId) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $model = $this->modelsService->updateModelFromModelDTO($model, ModelDTO::fromWpUpdateRequest($request, $model, $model->customer_id), $model->customer_id);

        $response = new ModelResource($model);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function destroy(int $customerId, Model $model): Response
    {
        if (! $model || (int) $model->customer?->wp_id !== $customerId) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $model->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function storeFromUpload(Request $request): JsonResponse
    {
        return response()->json($request->toArray());
    }
}
