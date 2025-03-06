<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Model;
use App\Models\ShopOwnerAuth;
use App\Nova\Settings\Shipping\CustomsItemSettings;
use App\Nova\Settings\Shipping\ParcelSettings;
use App\Services\Admin\CalculatePricesService;
use Illuminate\Support\Collection;

class ListingDTO
{
    public function __construct(
        public int $shopId,
        public ?int $listingId,
        public ?string $state,
        public int $quantity,
        public string $title,
        public string $description,
        public float $price,
        public ?string $whoMade,
        public ?string $whenMade,
        public int $taxonomyId,
        public int $shippingProfileId,
        public int $returnPolicyId,
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

        $price = app()->environment() !== 'production' ?
            0.18 :
            (new CalculatePricesService())->calculatePriceOfModel(
                price: $model->material->prices->first(),
                materialVolume: $model->model_volume_cc,
                surfaceArea: $model->model_surface_area_cm2,
            );

        return new self(
            shopId: $shopOwnerAuth->shop_oauth['shop_id'],
            listingId: null,
            state: null,
            quantity: 1,
            title: $model->model_name ?? $model->name,
            description: '3D print model: ' . ($model->model_name ?? $model->name),
            price: $price,
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: 12380, // 3D Printer Files
            shippingProfileId: 262651954760,
            returnPolicyId: 1356324035838,
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
