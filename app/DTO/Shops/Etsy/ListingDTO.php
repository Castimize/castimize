<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Model;
use App\Models\ShopOwnerAuth;
use App\Nova\Settings\Shipping\CustomsItemSettings;
use App\Nova\Settings\Shipping\ParcelSettings;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\CurrencyService;
use App\Services\Etsy\EtsyService;
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
        $shopOauth = $shopOwnerAuth->shop_oauth;
        $customsItemSettings = new CustomsItemSettings();
        $parcelSettings = new ParcelSettings();

        $price = app()->environment() !== 'production' ?
            0.18 :
            (new CalculatePricesService())->calculatePriceOfModel(
                price: $model->material->prices->first(),
                materialVolume: (float) $model->model_volume_cc,
                surfaceArea: (float) $model->model_surface_area_cm2,
            );

        if (app()->environment() === 'production' && $shopOauth['shop_currency'] !== config('app.currency')) {
            /** @var CurrencyService $currencyService */
            $currencyService = app(CurrencyService::class);
            $price = $currencyService->convertCurrency(config('app.currency'), $shopOauth['shop_currency'], $price);
        }

        return new self(
            shopId: $shopOauth['shop_id'],
            listingId: $model->shopListingModel?->listing_id ?? null,
            state: null,
            quantity: 1,
            title: $model->model_name ?? $model->name,
            description: '3D print model: ' . ($model->model_name ?? $model->name),
            price: $price,
            whoMade: 'i_did',
            whenMade: 'made_to_order',
            taxonomyId: (int) ($shopOauth['default_taxonomy_id'] ?? 12380), // 3D Printer Files
            shippingProfileId: $shopOauth['shop_shipping_profile_id'] ?? null,
            returnPolicyId: $shopOauth['shop_return_policy_id'] ?? null,
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
