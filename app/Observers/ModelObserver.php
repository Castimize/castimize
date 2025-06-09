<?php

namespace App\Observers;

use App\Enums\Shops\ShopOwnerShopsEnum;
use App\Models\Model;
use App\Services\Etsy\EtsyService;
use Exception;
use Illuminate\Support\Facades\Log;

class ModelObserver
{
    /**
     * Handle the Model "creating" event.
     */
    public function creating(Model $model): void
    {
    }

    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        //$this->syncModelToShop($model);
    }

    /**
     * Handle the Model "updating" event.
     */
    public function updating(Model $model): void
    {
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        $this->syncModelToShop($model);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
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

    private function syncModelToShop(Model $model): void
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
}
