<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Model;
use App\Models\ShopOwnerAuth;
use App\Nova\Settings\Shipping\CustomsItemSettings;
use App\Nova\Settings\Shipping\ParcelSettings;
use App\Services\Admin\CalculatePricesService;
use App\Services\Etsy\EtsyService;
use Illuminate\Support\Collection;

class ListingDTO
{
    public function __construct(
        public int $shopId,
        public ?int $listingId,
        public int $quantity,
        public string $title,
        public string $description,
        public float $price,
        public ?string $whoMade,
        public ?string $whenMade,
        public int $taxonomyId,
        public ?array $materials,
        public ?float $itemWeight,
        public ?float $itemLength,
        public ?float $itemWidth,
        public ?float $itemHeight,
        public ?string $itemWeightUnit,
        public ?string $itemDimensionsUnit,
        public ?int $processingMin,
        public ?int $processingMax,
        /** @var Collection<ListingImageDTO> */
        public ?Collection $listingImages,
    ) {
    }

    public static function fromModel(ShopOwnerAuth $shopOwnerAuth, Model $model): self
    {
        $customsItemSettings = new CustomsItemSettings();
        $parcelSettings = new ParcelSettings();
        $taxanomies = (new EtsyService())->getSellerTaxonomy($shopOwnerAuth);
        return new self(
            shopId: $shopOwnerAuth->shop_oauth['shop_id'],
            listingId: null,
            quantity: 1,
            title: $model->model_name ?? $model->name,
            description: '',
            price: (new CalculatePricesService())->calculatePriceOfModel(
                price: $model->material->prices->first(),
                materialVolume: $model->model_volume_cc,
                surfaceArea: $model->model_surface_area_cm2,
            ),
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: null,
            materials: [$model->material->name],
            itemWeight: $model->model_box_volume * $model->material->density + $customsItemSettings->bag,
            itemLength: $model->model_x_length,
            itemWidth: $model->model_y_length,
            itemHeight: $model->model_z_length,
            itemWeightUnit: $parcelSettings->massUnit,
            itemDimensionsUnit: $parcelSettings->distanceUnit,
            processingMin: $model->material->dc_lead_time + ($model->customer?->country?->logisticsZone?->shippingFee?->default_lead_time ?? 0),
            processingMax: null,
            listingImages: null,
        );
    }
}
