<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Enums\Admin\CurrencyEnum;
use App\Models\Country;
use App\Models\Shop;
use App\Services\Admin\CurrencyService;

class ShippingProfileDestinationDTO
{
    public function __construct(
        public int $shopId,
        public int $shippingProfileId,
        public ?int $shippingProfileDestinationId,
        public float $primaryCost,
        public float $secondaryCost,
        public string $destinationCountryIso,
        public int $minDeliveryDays,
        public int $maxDeliveryDays,
    ) {
    }

    public static function fromCountry(Shop $shop, Country $country, int $shippingProfileId, ?int $shippingProfileDestinationId = null): self
    {
        $rate = $country->logisticsZone->shippingFee->default_rate;
        if (
            app()->environment() === 'production' &&
            array_key_exists('shop_currency', $shop->shop_oauth) &&
            $shop->shop_oauth['shop_currency'] !== config('app.currency') &&
            in_array(CurrencyEnum::from($shop->shop_oauth['shop_currency']), CurrencyEnum::cases(), true)
        ) {
            /** @var CurrencyService $currencyService */
            $currencyService = app(CurrencyService::class);
            $rate = $currencyService->convertCurrency(config('app.currency'), $shop->shop_oauth['shop_currency'], $country->logisticsZone->shippingFee->default_rate);
        }

        return new self(
            shopId: $shop->shop_oauth['shop_id'],
            shippingProfileId: $shippingProfileId,
            shippingProfileDestinationId: $shippingProfileDestinationId,
            primaryCost: $rate,
            secondaryCost: 0.00,
            destinationCountryIso: $country->alpha2,
            minDeliveryDays: 2,
            maxDeliveryDays: 6,
        );
    }
}
