<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\DTO\Shops\Etsy\ListingDTO;
use App\Models\Model;
use App\Models\ShopListingModel;
use App\Models\ShopOwnerAuth;

class ShopListingModelService
{
    public function createShopListingModel(ShopOwnerAuth $shopOwnerAuth, Model $model, ListingDTO $listingDTO): ShopListingModel
    {
        $model->shopListingModel()->create([
            'shop_owner_id' => $shopOwnerAuth->shop_owner_id,
            'shop_owner_auth_id' => $shopOwnerAuth->id,
            'shop_listing_id' => $listingDTO->listingId,
            'shop_listing_image_id' => $listingDTO->listingImages ? $listingDTO->listingImages->first()->listingImageId : null,
            'state' => $listingDTO->state,
        ]);
    }
}
