<?php

declare(strict_types=1);

namespace App\Jobs\Etsy;

use App\Models\Customer;
use App\Models\Shop;
use App\Services\Etsy\EtsyService;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncListings implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private Shop $shopOwnerAuth,
    ) {
    }

    public function handle(): void
    {
        $etsyService = (new EtsyService());
        /** @var Customer $customer */
        $customer = $this->shopOwnerAuth->shopOwner->customer;
        $customerModels = $customer->models()->doesntHave('shopListingModel')->get();

        $models = [];
        foreach ($customerModels as $model) {
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

        $etsyService->syncListings($this->shopOwnerAuth, $models);
    }
}
