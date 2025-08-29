<?php

namespace App\Observers;

use App\Models\Currency;
use App\Models\Model;
use App\Models\Price;
use App\Services\Admin\ModelsService;

class PriceObserver
{
    /**
     * Handle the Price "creating" event.
     */
    public function creating(Price $price): void
    {
        if ($price->currency_code && $price->currency === null) {
            $currency = Currency::where('code', $price->currency_code)->first();
            if ($currency) {
                $price->currency_id = $currency->id;
            }
        }
    }

    /**
     * Handle the Price "updating" event.
     */
    public function updating(Price $price): void
    {
        if ($price->currency_code && $price->currency === null) {
            $currency = Currency::where('code', $price->currency_code)->first();
            if ($currency) {
                $price->currency_id = $currency->id;
            }
        }
    }

    //    public function updated(Price $price): void
    //    {
    //        $modelsService = new ModelsService();
    //        $material = $price->material;
    //        $models = Model::with(['materials', 'customer.shopOwner.shops'])
    //            ->has('shopListingModel')
    //            ->whereHas('materials', function ($query) use ($material) {
    //                $query->where('id', $material->id);
    //            })->get();
    //
    //        foreach ($models as $model) {
    //            $modelsService->syncModelToShop($model);
    //        }
    //    }
}
