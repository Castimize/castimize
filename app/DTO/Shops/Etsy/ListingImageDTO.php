<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Model;
use Illuminate\Support\Facades\Storage;

readonly class ListingImageDTO
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
    ) {
    }

    public static function fromModel(int $shopId, Model $model, int $listingImageId = null): self
    {
        $thumb = sprintf('%s.thumb.png', str_replace('_resized', '', $model->file_name));
        return new self(
            shopId: $shopId,
            listingId: $model->shopListingModel?->shop_listing_id,
            image: Storage::disk(env('FILESYSTEM_DISK'))->exists($thumb) ? sprintf('%s/%s', env('CLOUDFLARE_R2_URL'), $thumb) : '',
//            image: Storage::disk(env('FILESYSTEM_DISK'))->exists($thumb) ? Storage::disk(env('FILESYSTEM_DISK'))->get($thumb) : '',
            listingImageId: $listingImageId,
            altText: $model->model_name ?? $model->name,
        );
    }
}
