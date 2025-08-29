<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Enums\Admin\CurrencyEnum;
use App\Models\Material;
use App\Models\Model;
use App\Models\Shop;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\CurrencyService;
use App\Services\Admin\HelperService;

readonly class ListingInventoryDTO
{
    public function __construct(
        public ?int $listingId,
        public string $sku,
        public string $name,
        public float $price,
        public int $quantity,
        public CurrencyEnum $currency,
        public bool $isEnabled,
    ) {}

    public static function fromModel(Shop $shop, Material $material, Model $model, ?int $listingId): self
    {
        $shopOauth = $shop->shop_oauth;

        $price = app()->environment() !== 'production' ?
            0.18 :
            (new CalculatePricesService)->calculatePriceOfModel(
                price: $material->prices->first(),
                materialVolume: (float) $model->model_volume_cc,
                surfaceArea: (float) $model->model_surface_area_cm2,
            );

        if (app()->environment() === 'production' && $shopOauth['shop_currency'] !== config('app.currency')) {
            /** @var CurrencyService $currencyService */
            $currencyService = app(CurrencyService::class);
            $price = $currencyService->convertCurrency(config('app.currency'), $shopOauth['shop_currency'], $price);
        }

        return new self(
            listingId: $listingId,
            sku: 'CAST-'.app(HelperService::class)->generateSku($material->name, (int) $material->wp_id),
            name: $material->name,
            price: $price,
            quantity: 999,
            currency: CurrencyEnum::from($shopOauth['shop_currency']),
            isEnabled: false,
        );
    }
}
