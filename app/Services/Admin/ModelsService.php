<?php

namespace App\Services\Admin;

use App\DTO\Model\ModelDTO;
use App\DTO\Shops\Etsy\ListingDTO;
use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Http\Resources\ModelResource;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Model;
use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ModelsService
{
    public function getModelsPaginated($request, Customer $customer)
    {
        // Page Length
        $pageNumber = ( $request->start / $request->length ) + 1;
        $pageLength = (int) $request->length;
        $skip = (int) (($pageNumber - 1) * $pageLength);
        $orderColumn = $request->order_column;
        $orderDir = $request->order_dir;
        $searchValue = $request->search_value;
        $key = sprintf('%s-%s-%s-%s-%s-%s', $pageNumber, $pageLength, $skip, $orderColumn, $orderDir, $searchValue);

        return Cache::remember($key, 60, function () use ($customer, $pageLength, $skip, $orderColumn, $orderDir, $searchValue) {
            $query = "SELECT models.id, IFNULL(model_name, models.name) as order_model_name
                      FROM models
                      LEFT JOIN material_model ON models.id = material_model.model_id
                      LEFT JOIN materials ON material_model.material_id = materials.id
                      WHERE customer_id = {$customer->id}
                      and models.deleted_at IS NULL
                      {{{search}}}
                      GROUP BY models.name, model_name, model_scale
                      {{{order}}}
                      {{{limit}}}";

            if ($orderColumn) {
                $mapper = [
                    'id' => 'id',
                    'name' => 'order_model_name',
                    'material' => 'material_name',
                    'material_volume' => 'model_volume_cc',
                    'surface_area' => 'model_surface_area_cm2',
                    'scale' => 'model_scale',
                    'categories' => 'categories',
                    'link' => 'id',
                ];

                if (! isset($mapper, $orderColumn)) {
                    $query = str_replace(['{{{order}}}'], [' ORDER BY order_model_name ASC '], $query);
                } elseif ($mapper[$orderColumn] === 'name') {
                    $query = str_replace(['{{{order}}}'], [" ORDER BY order_model_name {$orderDir} "], $query);
                } else {
                    $query = str_replace(['{{{order}}}'], [" ORDER BY {$mapper[$orderColumn]} {$orderDir} "], $query);
                }
            }

            $countTotalQuery = str_replace(['{{{search}}}', '{{{limit}}}'], ['', ''], $query);
            $recordsTotal = count(DB::select($countTotalQuery));
            $recordsFiltered = $recordsTotal;

            if (! empty($searchValue)) {
                $searchQuery = " AND (
                    models.name LIKE '%{$searchValue}%'
                    OR model_name LIKE '%{$searchValue}%'
                    OR model_volume_cc LIKE '%{$searchValue}%'
                    OR model_x_length LIKE '%{$searchValue}%'
                    OR model_y_length LIKE '%{$searchValue}%'
                    OR model_z_length LIKE '%{$searchValue}%'
                    OR model_surface_area_cm2 LIKE '%{$searchValue}%'
                    OR categories LIKE '%{$searchValue}%'
                    OR materials.name LIKE '%{$searchValue}%'
                ) ";

                $query = str_replace(['{{{search}}}'], [$searchQuery], $query);
                $countFilteredQuery = str_replace(['{{{limit}}}'], [''], $query);
                $recordsFiltered = count(DB::select($countFilteredQuery));
            } else {
                $query = str_replace(['{{{search}}}'], [''], $query);
            }

            $query = str_replace(['{{{limit}}}'], [" LIMIT {$pageLength} OFFSET {$skip}"], $query);
            $modelsToShow = array_column(DB::select($query), 'id');
            $modelsToShowAsString = implode(',', $modelsToShow);

            $modelsQuery = Model::with(['materials.prices', 'customer.shopOwner', 'shopListingModel'])
                ->whereIn('id', $modelsToShow);

            if ($modelsToShowAsString) {
                $modelsQuery->orderByRaw("FIELD(id, $modelsToShowAsString)");
            }

            $models = $modelsQuery->get();
            return [
                'items' => ModelResource::collection($models),
                'filtered' => $recordsFiltered,
                'total' => $recordsTotal,
            ];
        });
    }

    public function storeModelFromApi($request, ?Customer $customer = null): Model|null
    {
        $scale = $request->scale ? number_format(round((float) $request->scale, 4), 4) : 1;

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

        $fileNameThumb = sprintf('%s%s.thumb.png', env('APP_SITE_STL_UPLOAD_DIR'), str_replace('_resized', '', $fileName));
        $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $fileName);
        $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
        $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
        $fileHeaders = get_headers($fileUrl);
        $withoutResizedFileName = str_replace('_resized', '', $fileName);

        try {
            // Check files exists on local storage of site and not on R2
            if (! str_contains($fileHeaders[0], '404') && ! Storage::disk('s3')->exists($fileName)) {
                Storage::disk('s3')->put($fileName, file_get_contents($fileUrl));
            }
            // Check files exists on local storage of site and not on R2 (without resized
            if (! str_contains($fileHeaders[0], '404') && ! Storage::disk('s3')->exists($withoutResizedFileName)) {
                Storage::disk('s3')->put($withoutResizedFileName, file_get_contents($fileUrl));
            }
            // Check file thumb exists on local storage of site and not on R2
            if (! str_contains($fileHeaders[0], '404') && ! Storage::disk('s3')->exists($fileNameThumb)) {
                Storage::disk('s3')->put($fileNameThumb, file_get_contents($fileThumb));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        if ($customer) {
            $model = $customer->models->where('name', $request->original_file_name)
                ->where('model_scale', $scale ?? 1)
                ->first();

            if ($model) {
                return $model;
            }
        } else {
            $model = Model::where('name', $request->original_file_name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $fileName)
                ->where('model_scale', $scale ?? 1)
                ->first();
        }

        if ($model) {
            if (empty($model->thumb_name)) {
                $model->thumb_name = $fileNameThumb;
                $model->save();
            }
            if (empty($model->customer_id) && $customer) {
                $model->customer_id = $customer->id;
                $model->save();
            }

            $model->materials()->sync($material->id);

            return $model;
        }

        $model = Model::create([
            'customer_id' => $customer?->id,
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
            'model_scale' => $scale,
            'meta_data' => $request->meta_data ?? null,
            'categories' => $categories,
        ]);

        $model->materials()->attach($material->id);

        return $model;
    }

    public function storeModelFromModelDTO(ModelDTO $modelDTO, ?Customer $customer = null): Model|null
    {
        $material = Material::where('wp_id', $modelDTO->wpId)->first();

        $fileNameThumb = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $modelDTO->thumbName);
        $fileName = sprintf('%s%s', env('APP_SITE_STL_UPLOAD_DIR'), $modelDTO->fileName);
        $fileUrl = sprintf('%s/%s', env('APP_SITE_URL'), $fileName);
        $fileThumb = sprintf('%s/%s', env('APP_SITE_URL'), $fileNameThumb);
        $fileHeaders = get_headers($fileUrl);
        $withoutResizedFileName = str_replace('_resized', '', $fileName);

        try {
            // Check files exists on local storage of site and not on R2
            if (! str_contains($fileHeaders[0], '404') && ! Storage::disk('s3')->exists($fileName)) {
                Storage::disk('s3')->put($fileName, file_get_contents($fileUrl));
            }
            // Check files exists on local storage of site and not on R2 (without resized
            if (! str_contains($fileHeaders[0], '404') && ! Storage::disk('s3')->exists($withoutResizedFileName)) {
                Storage::disk('s3')->put($withoutResizedFileName, file_get_contents($fileUrl));
            }
            // Check file thumb exists on local storage of site and not on R2
            if (! $modelDTO->uploadedThumb && ! str_contains($fileHeaders[0], '404') && ! Storage::disk('s3')->exists($fileNameThumb)) {
                Storage::disk('s3')->put($fileNameThumb, file_get_contents($fileThumb));
            }
        } catch (Exception $e) {
            Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
        }

        if ($customer) {
            $model = $customer->models->where('name', $modelDTO->name)
                ->where('model_scale', $modelDTO->modelScale)
                ->first();

            if ($model && ($model->model_name === $modelDTO->modelName || empty($model->model_name))) {
                if (empty($model->model_name)) {
                    $model->model_name = $modelDTO->modelName;
                }
                if ($modelDTO->categories !== null) {
                    $model->categories = $modelDTO->categories;
                }

                if ($modelDTO->uploadedThumb) {
                    $model->thumb_name = $fileNameThumb;
                }
                $model->save();

                $model->materials()->syncWithoutDetaching([$material->id]);
                $model->refresh();

                return $this->isShopOwnerModel(
                    model: $model,
                    modelDTO: $modelDTO,
                );
            }
        } else {
            $model = Model::where('name', $modelDTO->name)
                ->where('file_name', 'wp-content/uploads/p3d/' . $modelDTO->fileName)
                ->where('model_scale', $modelDTO->modelScale)
                ->first();

            if ($model) {
                $model->model_name = $modelDTO->modelName;
                $model->categories = $modelDTO->categories;
                $model->thumb_name = $fileNameThumb;

                if (empty($model->customer_id) && $customer) {
                    $model->customer_id = $customer->id;
                }
                $model->save();

                $model->materials()->syncWithoutDetaching([$material->id]);

                return $this->isShopOwnerModel(
                    model: $model,
                    modelDTO: $modelDTO,
                );
            }
        }

        $model = Model::create([
            'customer_id' => $customer?->id,
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

        return $this->isShopOwnerModel(
            model: $model,
            modelDTO: $modelDTO,
        );
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

        $model->materials()->sync($modelDTO->materials);

        $model = $this->isShopOwnerModel(
            model: $model,
            modelDTO: $modelDTO,
        );

        return $model;
    }

    private function isShopOwnerModel(Model $model, ModelDTO $modelDTO)
    {
        $etsyService = (new EtsyService());

        if (($modelDTO->shopListingId || $model->shopListingModel) && $model->customer->shopOwner) {
            $shop = $model->customer->shopOwner->shops->where('shop', ShopOwnerShopsEnum::Etsy->value)
                ->where('active', true)
                ->first();
            if ($shop) {
                $shopListingId = $modelDTO->shopListingId ?? $model->shopListingModel->shop_listing_id ?? null;
                if ($shopListingId && ! empty($shopListingId)) {
                    $listing = $etsyService->getListing($shop, $shopListingId);
                    if (! $listing) {
                        Log::error('Listing not found');
                        $model->load(['materials', 'customer.shopOwner.shops', 'shopListingModel']);
                        return $model;
                    }
                    $listingImages = $etsyService->getListingImages($shop, $listing->listing_id);

                    $listingDTO = ListingDTO::fromModel(
                        shop: $shop,
                        model: $model,
                        listingId: $listing->listing_id,
                        taxonomyId: $modelDTO->shopTaxonomyId ?? $listing->taxonomy_id,
                        listing: $listing,
                        listingImages: $listingImages ? collect($listingImages->data) : null,
                    );
                    if ($model->shopListingModel) {
                        (new ShopListingModelService())->updateShopListingModel(
                            shopListingModel: $model->shopListingModel,
                            listingDTO: $listingDTO,
                        );
                    } else {
                        (new ShopListingModelService())->createShopListingModel(
                            shop: $shop,
                            model: $model,
                            listingDTO: $listingDTO,
                        );
                    }

                    $model->load(['materials', 'customer.shopOwner.shops', 'shopListingModel']);

                    $this->syncModelToShop($model);
                }
            }
        }

        return $model;
    }

    public function syncModelToShop(Model $model): void
    {
        $shops = $model->customer?->shopOwner?->shops;
        if ($shops) {
            foreach ($shops as $shop) {
                if ($shop->active && $shop->shop === ShopOwnerShopsEnum::Etsy->value) {
                    try {
                        (new EtsyService())->syncListing($shop, $model);
                    } catch (Exception $e) {
                        Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                    }
                }
            }
        }
    }

    public function deleteModelFromShop(Model $model): void
    {
        $shops = $model->customer?->shopOwner?->shops;
        if ($shops) {
            foreach ($shops as $shop) {
                if ($shop->active && $shop->shop === ShopOwnerShopsEnum::Etsy->value && $model->has('shopListingModel')) {
                    try {
                        (new EtsyService())->deleteListing($shop, $model->shopListingModel->shop_listing_id);
                    } catch (Exception $e) {
                        Log::error($e->getMessage() . PHP_EOL . $e->getTraceAsString());
                    }
                }
            }
        }
    }
}
