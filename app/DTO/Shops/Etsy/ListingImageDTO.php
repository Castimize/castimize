<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Model;

class ListingImageDTO
{
    public function __construct(
        public int $shopId,
        public int $listingId,
        public ?int $listingImageId = null,
        public string $altText = '',
        public int $rank = 1,
        public bool $overwrite = false,
        public bool $isWatermarked = false,
    ) {
    }

    public static function fromModel(int $shopId, Model $model): self
    {
        return new self(
            shopId: $shopId,
            listingId: $model->shopListingModel->shop_listing_id,
            listingImageId: null,
            altText: $model->model_name ?? $model->name,
        );
    }
}
