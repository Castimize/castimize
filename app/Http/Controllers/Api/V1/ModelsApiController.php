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
use Illuminate\Support\Str;
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

    public function showModelsWpCustomerPaginated(Request $request, int $customerId): JsonResponse
    {
        $customer = Customer::with('models.material')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $customerModels = $customer->models();

        if ($request->search_value) {
            $customerModels->where(function ($query) use ($request) {
                $query->where('models.name', 'like', '%' . $request->search_value . '%')
                    ->orWhere('model_name', 'like', '%' . $request->search_value . '%')
                    ->orWhere('model_volume_cc', 'like', '%' . $request->search_value . '%')
                    ->orWhere('model_surface_area_cm2', 'like', '%' . $request->search_value . '%')
                    ->orWhereJsonContains('categories', $request->search_value);
            })->orWhereHas('material', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->search_value . '%');
            })->where('customer_id', $customerId);
//            $customerModels = $customerModels->filter(function ($model) use ($request) {
//                return (Str::contains($model->name, $request->search_value) ||
//                   Str::contains($model->model_name, $request->search_value) ||
//                    Str::contains($model->model_volume_cc, $request->search_value) ||
//                    Str::contains($model->model_surface_area_cm2, $request->search_value) ||
//                    Str::contains($model->material->name, $request->search_value)
//                );
//            })->filter(function ($model) use ($request) {
//                if (is_array($model->categories)) {
//                    foreach ($model->categories as $category) {
//                        if (Str::contains($category, $request->search_value)) {
//                            return true;
//                        }
//                    }
//
//                    return false;
//                }
//
//                return true;
//            });
        }

        if ($request->order_column) {
            $mapper = [
                'id' => 'id',
                'name' => 'name',
                'material' => 'material_name',
                'material_volume' => 'model_volume_cc',
                'surface_area' =>'model_surface_area_cm2',
                'scale' => 'model_scale',
                'categories' => 'categories',
            ];

            if ($mapper[$request->order_column] === 'name') {
                $customerModels->orderBy('model_name', $request->order_dir)
                    ->orderBy('name', $request->order_dir);
            } elseif ($mapper[$request->order_column] === 'material_name') {
                $customerModels->join('materials', 'models.material_id', '=', 'materials.id')
                    ->orderBy('materials.name', $request->order_dir);
            } else {
                $customerModels->orderBy($mapper[$request->order_column], $request->order_dir);
            }
        }

        $customerModels = $customerModels->offset($request->start)
            ->limit($request->length)
            ->distinct([
                'model_name',
                'name',
                'material_id',
                'model_volume_cc',
                'model_surface_area_cm2',
                'model_box_volume',
                'model_x_length',
                'model_y_length',
                'model_z_length',
            ]);

//        $models = [];
//        foreach ($customerModels->get() as $model) {
//            $key = sprintf('%s-%s-%s-%s-%s-%s-%s-%s-%s',
//                $model->model_name,
//                $model->name,
//                $model->material_id,
//                $model->model_volume_cc,
//                $model->model_surface_area_cm2,
//                $model->model_box_volume,
//                $model->model_x_length,
//                $model->model_y_length,
//                $model->model_z_length
//            );
//            if (!array_key_exists($key, $models)) {
//                $models[$key] = $model;
//            }
//        }
//        $models = collect($models);
        $models = $customerModels->get();

        $response = ModelResource::collection($models);
        LogRequestService::addResponse(request(), $response);
        return response()->json(['items' => $response, 'total' => $customer->models->count()]);
    }

    public function storeModelWp(Request $request): JsonResponse
    {
        $model = $this->modelsService->storeModelFromApi($request);

        $response = new ModelResource($model);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function getCustomModelName(int $customerId, Request $request): JsonResponse
    {
        $customer = Customer::with('models.material')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $upload = json_decode($request->upload, true, 512, JSON_THROW_ON_ERROR);
        [$materialId, $materialName] = explode('. ', $upload['3dp_options']['material_name']);

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
        $customer = Customer::with('models.material')->where('wp_id', $customerId)->first();
        if ($customer === null) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $newUploads = [];
        foreach (json_decode($request->uploads, true, 512, JSON_THROW_ON_ERROR) as $itemKey => $upload) {
            [$materialId, $materialName] = explode('. ', $upload['3dp_options']['material_name']);
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
                    $newUploads[$itemKey]['3dp_options']['thumbnail'] = Storage::disk(env('FILESYSTEM_DISK'))->exists($model->thumb_name) ? sprintf('%s/%s', env('CLOUDFLARE_R2_URL'), $model->thumb_name) : '/' . $model->thumb_name;
                }
                $newUploads[$itemKey]['3dp_options']['model_name_original'] = $model->model_name ?: $upload['3dp_options']['model_name_original'];
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
//        $model = $this->modelsService->storeModelFromApi($request, $customer);

        $response = new ModelResource($model);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function update(Request $request, int $customerId, Model $model): JsonResponse
    {
        ini_set('precision', 53);
        if (!$model || (int)$model->customer->wp_id !== $customerId) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $model = $this->modelsService->updateModelFromModelDTO($model, ModelDTO::fromWpUpdateRequest($request, $model, $model->customer_id), $model->customer_id);
//        $model = $this->modelsService->updateModelFromApi($request, $model, $model->customer_id);

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
