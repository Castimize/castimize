<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\DTO\Shops\Etsy\ListingDTO;
use App\Models\Model;
use App\Models\ShopListingModel;
use App\Models\Shop;

class ShopListingModelService
{
    public function createShopListingModel(Shop $shopOwnerAuth, Model $model, ListingDTO $listingDTO): ShopListingModel
    {
        return $model->shopListingModel()->create([
            'shop_owner_id' => $shopOwnerAuth->shop_owner_id,
            'shop_owner_auth_id' => $shopOwnerAuth->id,
            'taxonomy_id' => $listingDTO->taxonomyId,
            'shop_listing_id' => $listingDTO->listingId,
            'state' => $listingDTO->state,
        ]);
    }

    public function updateShopListingModel(ShopListingModel $shopListingModel, ListingDTO $listingDTO): ShopListingModel
    {
        $shopListingModel->update([
            'taxonomy_id' => $listingDTO->taxonomyId,
            'state' => $listingDTO->state,
            ]);
    }
}
