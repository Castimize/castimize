<?php

declare(strict_types=1);

namespace App\DTO\Shops\Etsy;

use App\Nova\Settings\Shipping\DcSettings;
use Illuminate\Support\Collection;

readonly class ShippingProfileDTO
{
    public function __construct(
        public int $shopId,
        public ?int $shippingProfileId,
        public string $title,
        public string $originCountryIso,
        public float $primaryCost,
        public float $secondaryCost,
        public string $destinationCountryIso,
        public string $originPostalCode,
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
        $dcSettings = (new DcSettings());

        return new self(
            shopId: $shopId,
            shippingProfileId: null,
            title: 'Castimize shipping profile',
            originCountryIso: 'NL',
            primaryCost: 7.75,
            secondaryCost: 0.00,
            destinationCountryIso: 'NL',
            originPostalCode: $dcSettings->postalCode,
            minProcessingTime: 1,
            maxProcessingTime: 3,
            processingTimeUnit: 'weeks',
            minDeliveryDays: 2,
            maxDeliveryDays: 6,
            shippingProfileDestinations: collect(),
        );
    }
}
