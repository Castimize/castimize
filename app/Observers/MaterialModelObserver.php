<?php

namespace App\Observers;

use App\Models\MaterialModel;
use App\Services\Admin\ModelsService;
use Illuminate\Support\Facades\Log;

class MaterialModelObserver
{
    /**
     * Handle the MaterialModel "deleted" event.
     *
     * When a material is detached from a model, sync the model's Etsy listing
     * so the removed material gets disabled in the shop.
     */
    public function deleted(MaterialModel $materialModel): void
    {
        $model = $materialModel->model()->with([
            'materials',
            'shopListingModel',
            'customer.shopOwner.shops',
        ])->first();

        if (! $model || ! $model->shopListingModel) {
            return;
        }

        Log::info('Material detached from model — triggering Etsy listing sync', [
            'model_id' => $model->id,
            'material_id' => $materialModel->material_id,
        ]);

        (new ModelsService)->syncModelToShop($model);
    }
}
