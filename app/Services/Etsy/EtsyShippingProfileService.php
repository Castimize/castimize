<?php

namespace App\Services\Etsy;

use AllowDynamicProperties;
use App\DTO\Shops\Etsy\ListingDTO;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Models\Shop;
use App\Services\Etsy\Resources\Listing;
use Etsy\Collection;
use Etsy\Etsy;
use Etsy\Resources\ShippingDestination;
use Etsy\Resources\ShippingProfile;

#[AllowDynamicProperties]
class EtsyShippingProfileService
{
    public function __construct(
        protected Shop $shop,
    ) {
        $this->etsy = new Etsy($this->shop->shop_oauth['client_id'], $this->shop->shop_oauth['access_token']);
    }

    public function getShippingProfile()
    {
        return ShippingProfile::get(
            shop_id: $this->shop->shop_oauth['shop_id'],
            profile_id: $this->shop->shop_oauth['shipping_profile_id'],
        );
    }

    public function getShippingProfiles()
    {
        return ShippingProfile::all(
            shop_id: $this->shop->shop_oauth['shop_id'],
        );
    }

    public function createShippingProfile(ShippingProfileDTO $shippingProfileDTO): ShippingProfileDTO
    {
        $shippingProfile = ShippingProfile::create(
            shop_id: $this->shop->shop_oauth['shop_id'],
            data: [
                'title' => $shippingProfileDTO->title,
                'origin_country_iso' => $shippingProfileDTO->originCountryIso,
                'primary_cost' => $shippingProfileDTO->primaryCost,
                'secondary_cost' => $shippingProfileDTO->secondaryCost,
                'destination_country_iso' => $shippingProfileDTO->destinationCountryIso,
                'origin_postal_code' => $shippingProfileDTO->originPostalCode,
                'min_processing_time' => $shippingProfileDTO->minProcessingTime,
                'max_processing_time' => $shippingProfileDTO->maxProcessingTime,
                'processing_time_unit' => $shippingProfileDTO->processingTimeUnit,
                'min_delivery_days' => $shippingProfileDTO->minDeliveryDays,
                'max_delivery_days' => $shippingProfileDTO->maxDeliveryDays,
            ],
        );

        $shippingProfileDTO->shippingProfileId = $shippingProfile?->shipping_profile_id;

        $this->addShippingProfileToShopOwnerShop($shippingProfile);

        return $shippingProfileDTO;
    }

    public function createShippingProfileDestination(ShippingProfileDestinationDTO $shippingProfileDestinationDTO): ShippingProfileDestinationDTO
    {
        $shippingDestination = ShippingDestination::create(
            shop_id: $this->shop->shop_oauth['shop_id'],
            profile_id: $shippingProfileDestinationDTO->shippingProfileId,
            data: [
                'primary_cost' => $shippingProfileDestinationDTO->primaryCost,
                'secondary_cost' => $shippingProfileDestinationDTO->secondaryCost,
                'destination_country_iso' => $shippingProfileDestinationDTO->destinationCountryIso,
                'min_delivery_days' => $shippingProfileDestinationDTO->minDeliveryDays,
                'max_delivery_days' => $shippingProfileDestinationDTO->maxDeliveryDays,
            ],
        );

        $shippingProfileDestinationDTO->shippingProfileDestinationId = $shippingDestination->shipping_profile_destination_id;

        return $shippingProfileDestinationDTO;
    }

    public function addShippingProfileToShopOwnerShop(?ShippingProfile $shippingProfile): void
    {
        $shopOauth = $this->shop->shop_oauth;
        if (! array_key_exists('shop_return_policy_id', $shopOauth) && $shippingProfile) {
            $shopOauth['shop_shipping_profile_id'] = $shippingProfile->shipping_profile_id;

            $this->shop->shop_oauth = $shopOauth;
            $this->shop->save();
        }
    }
}
