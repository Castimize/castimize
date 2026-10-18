<?php

namespace App\Services\Etsy;

use AllowDynamicProperties;
use App\DTO\Shops\Etsy\ShippingProfileDestinationDTO;
use App\DTO\Shops\Etsy\ShippingProfileDTO;
use App\Models\Country;
use App\Models\Shop;
use Etsy\Etsy;
use Etsy\Resources\ShippingDestination;
use Etsy\Resources\ShippingProfile;
use Exception;
use Illuminate\Support\Facades\Log;

#[AllowDynamicProperties]
class EtsyShippingProfileService
{
    private Etsy $etsy;

    public function __construct(
        protected Shop $shop,
    ) {
        $this->etsy = new Etsy(
            client_id: $shop->shop_oauth['client_id'],
            shared_secret: config('services.shops.etsy.client_secret'),
            api_key: $shop->shop_oauth['access_token'],
        );
    }

    public function getShippingProfile()
    {
        return ShippingProfile::get(
            shop_id: $this->shop->shop_oauth['shop_id'],
            profile_id: $this->shop->shop_oauth['shop_shipping_profile_id'],
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
        $countries = Country::with(['logisticsZone.shippingFee'])
            ->whereHas('logisticsZone', fn ($q) => $q->whereHas('shippingFee'))
            ->get();

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

        foreach ($countries as $country) {
            try {
                $shippingProfileDestinationDTO = $this->createShippingProfileDestination(
                    shippingProfileDestinationDTO: ShippingProfileDestinationDTO::fromCountry(
                        shop: $this->shop,
                        country: $country,
                        shippingProfileId: $shippingProfileDTO->shippingProfileId,
                    ),
                );

                $shippingProfileDTO->shippingProfileDestinations->push($shippingProfileDestinationDTO);
            } catch (Exception $exception) {
                // just continue with other countries
            }
        }

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

    public function updateShippingProfileDestination(ShippingProfileDestinationDTO $shippingProfileDestinationDTO): ShippingProfileDestinationDTO
    {
        $shippingDestination = ShippingDestination::update(
            shop_id: $this->shop->shop_oauth['shop_id'],
            profile_id: $shippingProfileDestinationDTO->shippingProfileId,
            destination_id: $shippingProfileDestinationDTO->shippingProfileDestinationId,
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

    public function syncShippingProfileDestinations(int $shippingProfileId): void
    {
        $shippingProfile = ShippingProfile::get(
            shop_id: $this->shop->shop_oauth['shop_id'],
            profile_id: $shippingProfileId,
        );

        $existingDestinations = [];
        foreach ($shippingProfile->shipping_profile_destinations as $destination) {
            $existingDestinations[$destination->destination_country_iso] = $destination->shipping_profile_destination_id;
        }

        $countries = Country::with(['logisticsZone.shippingFee'])
            ->whereHas('logisticsZone', fn ($q) => $q->whereHas('shippingFee'))
            ->get();

        foreach ($countries as $country) {
            try {
                $dto = ShippingProfileDestinationDTO::fromCountry(
                    shop: $this->shop,
                    country: $country,
                    shippingProfileId: $shippingProfileId,
                    shippingProfileDestinationId: $existingDestinations[$country->alpha2] ?? null,
                );

                if (isset($existingDestinations[$country->alpha2])) {
                    $this->updateShippingProfileDestination($dto);
                } else {
                    $this->createShippingProfileDestination($dto);
                }
            } catch (Exception $exception) {
                Log::warning('Failed to sync shipping profile destination', [
                    'shop_id' => $this->shop->id,
                    'country' => $country->alpha2,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
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
