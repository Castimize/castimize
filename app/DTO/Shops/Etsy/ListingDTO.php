<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Model;
use App\Models\Shop;
use App\Nova\Settings\Shipping\CustomsItemSettings;
use App\Nova\Settings\Shipping\ParcelSettings;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\CurrencyService;
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
        public ?Collection $materials,
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
        /** @var Collection<ListingInventoryDTO> */
        public ?Collection $listingInventory,
    ) {
    }

    public static function fromModel(Shop $shop, Model $model, ?int $listingId = null, ?int $taxonomyId = null, $listing = null, $listingImages = null): self
    {
        $shopOauth = $shop->shop_oauth;
        $customsItemSettings = new CustomsItemSettings();
        $parcelSettings = new ParcelSettings();

        $price = 0.00;
        foreach ($model->materials as $material) {
            $priceMaterial = app()->environment() !== 'production' ?
                0.18 :
                (new CalculatePricesService())->calculatePriceOfModel(
                    price: $material->prices->sortBy('price_volume_cc')->first(),
                    materialVolume: (float)$model->model_volume_cc,
                    surfaceArea: (float)$model->model_surface_area_cm2,
                );
            if ($price === 0.00 || $priceMaterial < $price) {
                $price = $priceMaterial;
            }
        }

        if (app()->environment() === 'production' && $shopOauth['shop_currency'] !== config('app.currency')) {
            /** @var CurrencyService $currencyService */
            $currencyService = app(CurrencyService::class);
            $price = $currencyService->convertCurrency(config('app.currency'), $shopOauth['shop_currency'], $price);
        }

        return new self(
            shopId: $shopOauth['shop_id'],
            listingId: $listing ? $listing->listing_id : ($listingId ?? $model->shopListingModel?->shop_listing_id ?? null),
            state: $listing ? $listing->state : null,
            quantity: 1,
            title: $model->model_name ?? $model->name,
            description: '3D print model: ' . ($model->model_name ?? $model->name),
            price: (int) ($price * 100),
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: (int) ($taxonomyId ?? ($listing ? $listing->taxonomy_id : ($shopOauth['default_taxonomy_id'] ?? 12380))), // 3D Printer Files
            shippingProfileId: $shopOauth['shop_shipping_profile_id'] ?? null,
            returnPolicyId: $shopOauth['shop_return_policy_id'] ?? null,
            materials: $model->materials,
            itemWeight: $model->model_box_volume * $model->material->density + $customsItemSettings->bag,
            itemLength: $model->model_x_length,
            itemWidth: $model->model_y_length,
            itemHeight: $model->model_z_length,
            itemWeightUnit: $parcelSettings->massUnit,
            itemDimensionsUnit: $parcelSettings->distanceUnit,
            processingMin: $model->material->dc_lead_time + ($model->customer?->country?->logisticsZone?->shippingFee?->default_lead_time ?? 0),
            processingMax: null,
            listingImages: $listingImages,
            listingInventory: $model->materials->map(function ($material) use ($shop, $model, $listing) {
                return ListingInventoryDTO::fromModel(
                    shop: $shop,
                    material: $material,
                    model: $model,
                    listingId: $listing ? $listing->listing_id : ($model->shopListingModel?->shop_listing_id ?? null),
                );
            }),
        );
    }
}
