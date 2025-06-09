<?php

namespace App\Services\Admin;

use App\DTO\Model\ModelDTO;
use App\DTO\Shops\Etsy\ListingDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Http\Resources\ModelResource;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Models\User;
use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ModelsService
{
    public function getModelsPaginated($request, Customer $customer): array
    {
        $customerModels = Model::with(['materials'])
            ->where('customer_id', $customer->id);

        if ($request->search_value) {
            $customerModels
                ->where(function ($query) use ($request) {
                    $query->where('models.name', 'like', '%' . $request->search_value . '%')
                        ->orWhere('model_name', 'like', '%' . $request->search_value . '%')
                        ->orWhere('model_volume_cc', 'like', '%' . $request->search_value . '%')
                        ->orWhere('model_x_length', 'like', '%' . $request->search_value . '%')
                        ->orWhere('model_y_length', 'like', '%' . $request->search_value . '%')
                        ->orWhere('model_z_length', 'like', '%' . $request->search_value . '%')
                        ->orWhere('model_surface_area_cm2', 'like', '%' . $request->search_value . '%')
                        ->orWhere('categories', 'like', '%' . $request->search_value . '%');
                })->orWhereHas('materials', function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->search_value . '%');
                });
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

            if (! array_key_exists($request->order_column, $mapper)) {
                $customerModels->orderBy('id');
            } elseif ($mapper[$request->order_column] === 'name') {
                $customerModels->orderBy('model_name', $request->order_dir)
                    ->orderBy('name', $request->order_dir);
            } elseif ($mapper[$request->order_column] === 'material_name') {
//                $customerModels->join('materials', 'models.material_id', '=', 'materials.id')
//                    ->orderBy('materials.name', $request->order_dir);
            } else {
                $customerModels->orderBy($mapper[$request->order_column], $request->order_dir);
            }
        }

        $customerModels->distinct([
                'model_name',
                'models.name',
//                'material_id',
                'model_volume_cc',
                'model_surface_area_cm2',
                'model_box_volume',
                'model_x_length',
                'model_y_length',
                'model_z_length',
            ]);

        $total = $customerModels->count();
        $models = $customerModels->offset($request->start)
            ->limit($request->length)
            ->get();

        return ['items' => ModelResource::collection($models), 'total' => $total];
    }

    public function storeModelFromApi($request, ?Customer $customer = null): Model|null
    {
        $systemUser = User::find(1);

        $material = Material::where('wp_id', $request->wp_id)->first();
        $fileName = $request->file_name;
        $categories = null;
        if ($request->has('categories')) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }

        if ($customer) {
            $model = $customer->models->where('name', $request->original_file_name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $fileName)
//                ->where('material_id', $material->id)
                ->where('model_volume_cc', $request->material_volume)
                ->first();

            if ($model && $model->model_name === $request->model_name) {
                return $model;
            }
        } else {
            $model = Model::where('name', $request->original_file_name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $fileName)
//                ->where('material_id', $material->id)
                ->where('model_volume_cc', $request->material_volume)
                ->first();
        }

        $fileNameThumb = sprintf('%s%s.thumb.png', env('APP_SITE_STL_UPLOAD_DIR'), str_replace('_resized', '', $fileName));
        $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $fileName);
        $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
        $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
        $fileHeaders = get_headers($fileUrl);
        $withoutResizedFileName = str_replace('_resized', '', $fileName);

        try {
            // Check files exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileName)) {
                Storage::disk('r2')->put($fileName, file_get_contents($fileUrl));
            }
            // Check files exists on local storage of site and not on R2 (without resized
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($withoutResizedFileName)) {
                Storage::disk('r2')->put($withoutResizedFileName, file_get_contents($fileUrl));
            }
            // Check file thumb exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileNameThumb)) {
                Storage::disk('r2')->put($fileNameThumb, file_get_contents($fileThumb));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        if ($model) {
            if (empty($model->thumb_name)) {
                $model->thumb_name = $fileNameThumb;
                $model->save();
            }

            $model->materials()->sync($material->id);

            return $model;
        }

        $model = Model::create([
            'customer_id' => $customer?->id,
//            'material_id' => $material->id,
            'model_name' => $request->model_name ?? null,
            'name' => $request->original_file_name,
            'file_name' => $fileName,
            'thumb_name' => $fileNameThumb,
            'model_volume_cc' => $request->material_volume,
            'model_x_length' => $request->x_dim,
            'model_y_length' => $request->y_dim,
            'model_z_length' => $request->z_dim,
            'model_surface_area_cm2' => $request->surface_area,
            'model_parts' => $request->model_parts ?? 1,
            'model_box_volume' => $request->box_volume,
            'model_scale' => $request->scale ?? 1,
            'meta_data' => $request->meta_data ?? null,
            'categories' => $categories,
        ]);

        $model->materials()->attach($material->id);

        return $model;
    }

    public function storeModelFromModelDTO(ModelDTO $modelDTO, ?Customer $customer = null): Model|null
    {
        $material = Material::where('wp_id', $modelDTO->wpId)->first();

        if ($customer) {
            $model = $customer->models->where('name', $modelDTO->name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $modelDTO->fileName)
//                ->where('material_id', $material->id)
                ->where('model_scale', $modelDTO->modelScale)
                ->first();

            if ($model && $model->model_name === $modelDTO->modelName) {
                return $model;
            }
        } else {
            $model = Model::where('name', $modelDTO->name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $modelDTO->fileName)
//                ->where('material_id', $material->id)
                ->where('model_scale', $modelDTO->modelScale)
                ->first();
        }

        $fileNameThumb = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $modelDTO->thumbName);
        $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $modelDTO->fileName);
        $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
        $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
        $fileHeaders = get_headers($fileUrl);
        $withoutResizedFileName = str_replace('_resized', '', $fileName);

        try {
            // Check files exists on local storage of site and not on R2
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileName)) {
                Storage::disk('r2')->put($fileName, file_get_contents($fileUrl));
            }
            // Check files exists on local storage of site and not on R2 (without resized
            if (!str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($withoutResizedFileName)) {
                Storage::disk('r2')->put($withoutResizedFileName, file_get_contents($fileUrl));
            }
            // Check file thumb exists on local storage of site and not on R2
            if (! $modelDTO->uploadedThumb && !str_contains($fileHeaders[0], '404') && !Storage::disk('r2')->exists($fileNameThumb)) {
                Storage::disk('r2')->put($fileNameThumb, file_get_contents($fileThumb));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        if ($model) {
            $model->thumb_name = $fileNameThumb;
            $model->save();

            $model->materials()->sync($material->id);

            return $model;
        }

        $model = Model::create([
            'customer_id' => $modelDTO->customerId,
//            'material_id' => $material->id,
            'model_name' => $modelDTO->modelName,
            'name' => $modelDTO->name,
            'file_name' => $fileName,
            'thumb_name' => $fileNameThumb,
            'model_volume_cc' => $modelDTO->modelVolumeCc,
            'model_x_length' => $modelDTO->modelXLength,
            'model_y_length' => $modelDTO->modelYLength,
            'model_z_length' => $modelDTO->modelZLength,
            'model_surface_area_cm2' => $modelDTO->surfaceArea,
            'model_parts' => $modelDTO->modelParts,
            'model_box_volume' => $modelDTO->modelBoxVolume,
            'model_scale' => $modelDTO->modelScale,
            'meta_data' => $modelDTO->metaData,
            'categories' => $modelDTO->categories,
        ]);

        $model->materials()->syncWithoutDetaching([$material->id]);

        return $model;
    }

    public function updateModelFromApi($request, Model $model, ?int $customerId = null): Model
    {
        $model->model_name = $request->model_name;
        $categories = null;
        if ($request->has('categories')) {
            $categories = [];
            foreach (explode(',', $request->categories) as $category) {
                $categories[] = [
                    'category' => $category,
                ];
            }
        }
        $model->categories = $categories;

        $model->save();

        return $model;
    }

    public function updateModelFromModelDTO(Model $model, ModelDTO $modelDTO, ?int $customerId = null): Model
    {
        $etsyService = (new EtsyService());

        $model->model_name = $modelDTO->modelName;
        if ($modelDTO->categories) {
            $model->categories = $modelDTO->categories;
        }

        if ($modelDTO->thumbName) {
            $fileNameThumb = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $modelDTO->thumbName);
            $model->thumb_name = $fileNameThumb;
        }

        $model->save();

        if ($modelDTO->shopListingId && $model->customer->shopOwner) {
            $shops = $model->customer->shopOwner->shops;
            $shop = $shops->where('shop', ShopOwnerShopsEnum::Etsy->value)
                ->where('active', true)
                ->first();
            if ($shop) {
                $listing = $etsyService->getListing($shop, $modelDTO->shopListingId);
                if (! $listing) {
                    throw new Exception('Listing not found');
                }
                $listingImages = $etsyService->getListingImages($shop, $listing->listing_id);

                if ($model->shopListingModel) {
                    (new ShopListingModelService())->updateShopListingModel(
                        shopListingModel: $model->shopListingModel,
                        listingDTO: ListingDTO::fromModel(
                            shop: $shop,
                            model: $model,
                            listingId: $modelDTO->shopListingId,
                            listing: $listing,
                            listingImages: $listingImages ? collect($listingImages->data) : null,
                        ),
                    );
                } else {
                    (new ShopListingModelService())->createShopListingModel(
                        shop: $shop,
                        model: $model,
                        listingDTO: ListingDTO::fromModel(
                            shop: $shop,
                            model: $model,
                            listingId: $modelDTO->shopListingId,
                            listing: $listing,
                            listingImages: $listingImages ? collect($listingImages->data) : null,
                        ),
                    );
                }
            }
        }

        return $model;
    }
}
