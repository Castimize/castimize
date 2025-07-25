<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Models\Country;

readonly class ShippingProfileDestinationDTO
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

    public static function fromCountry(int $shopId, Country $country, int $shippingProfileId): self
    {
        return new self(
            shopId: $shopId,
            shippingProfileId: $shippingProfileId,
            shippingProfileDestinationId: null,
            primaryCost: $country->logisticsZone->shippingFee->default_rate,
            secondaryCost: 0.00,
            destinationCountryIso: $country->alpha2,
            minDeliveryDays: 2,
            maxDeliveryDays: 6,
        );
    }
}
