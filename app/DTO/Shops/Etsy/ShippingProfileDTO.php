<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use Illuminate\Support\Collection;

class ShippingProfileDTO
{
    public function __construct(
        public int $shopId,
        public ?int $shippingProfileId,
        public string $title,
        public string $originCountryIso,
        public float $primaryCost,
        public float $secondaryCost,
        public int $minProcessingTime,
        public int $maxProcessingTime,
        public string $processingTimeUnit,
        public int $minDeliveryDays,
        public int $maxDeliveryDays,
        /** @var Collection<ShippingProfileDestinationDTO> */
        public ?Collection $shippingProfileDestinations = null,
    ) {
    }

    public static function fromShop(int $shopId): self
    {
        return new self(
            shopId: $shopId,
            shippingProfileId: null,
            title: 'Castimize shipping profile',
            originCountryIso: 'NL',
            primaryCost: 7.75,
            secondaryCost: 0.00,
            minProcessingTime: 7,
            maxProcessingTime: 18,
            processingTimeUnit: 'business_days',
            minDeliveryDays: 2,
            maxDeliveryDays: 6,
            shippingProfileDestinations: collect(),
        );
    }
}
