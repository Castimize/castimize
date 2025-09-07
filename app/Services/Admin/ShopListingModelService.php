<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\DTO\Shops\Etsy\ListingDTO;
use App\Models\Model;
use App\Models\Shop;
use App\Models\ShopListingModel;
use Illuminate\Support\Facades\Log;

class ShopListingModelService
{
    public function createShopListingModel(Shop $shop, Model $model, ListingDTO $listingDTO): ShopListingModel
    {
        Log::info('Creating shop listing model for listing '.$listingDTO->listingId);

        $shopListingModel = $model->shopListingModel()->create([
            'shop_owner_id' => $shop->shop_owner_id,
            'shop_id' => $shop->id,
            'taxonomy_id' => $listingDTO->taxonomyId,
            'shop_listing_id' => $listingDTO->listingId,
            'state' => $listingDTO->state,
        ]);
        Log::info('Created shop listing model '.$shopListingModel->id);

        return $shopListingModel;
    }

    public function updateShopListingModel(ShopListingModel $shopListingModel, ListingDTO $listingDTO): ShopListingModel
    {
        Log::info('Updating shop listing model '.$shopListingModel->id);
        $shopListingModel->update([
            'shop_listing_id' => $listingDTO->listingId,
            //            'taxonomy_id' => $listingDTO->taxonomyId,
            'shop_listing_image_id' => $listingDTO->listingImages ? $listingDTO->listingImages->first()->listing_image_id : $shopListingModel->shop_listing_image_id,
            'state' => $listingDTO->state,
        ]);
        Log::info('Updated shop listing model '.$shopListingModel->id);

        $shopListingModel->refresh();

        return $shopListingModel;
    }
}
