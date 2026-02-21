<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Data;

class ListingImageDTO extends Data
{
    public function __construct(
        public int $shopId,
        public ?int $listingId,
        public string $image,
        public ?int $listingImageId = null,
        public string $altText = '',
        public int $rank = 1,
        public bool $overwrite = false,
        public bool $isWatermarked = false,
    ) {}

    public static function fromModel(int $shopId, Model $model, ?int $listingImageId = null): self
    {
        $thumb = sprintf('%s.thumb.png', str_replace('_resized', '', $model->file_name));

        return new self(
            shopId: $shopId,
            listingId: $model->shopListingModel?->shop_listing_id,
            image: Storage::disk(config('filesystems.default'))->exists($thumb)
                ? sprintf('%s/%s', config('filesystems.disks.s3.url'), $thumb)
                : '',
            listingImageId: $listingImageId,
            altText: $model->model_name ?? $model->name,
        );
    }
}
