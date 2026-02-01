<?php

namespace App\Services\Etsy;

use AllowDynamicProperties;
use App\DTO\Shops\Etsy\ListingDTO;
use App\Models\Shop;
use App\Services\Etsy\Resources\Listing;
use Etsy\Collection;
use Etsy\Etsy;
use Exception;
use Illuminate\Support\Facades\Log;

#[AllowDynamicProperties]
class EtsyListingService
{
    public function __construct(
        protected Shop $shop,
    ) {
        $this->etsy = new Etsy(
            client_id: $shop->shop_oauth['client_id'],
            shared_secret: config('services.shops.etsy.client_secret'),
            api_key: $shop->shop_oauth['access_token'],
        );
    }

    public function getListings(): Collection
    {
        return Listing::allByShop(
            shop_id: $this->shop->shop_oauth['shop_id'],
        );
    }

    public function getListing(int $listingId)
    {
        return Listing::get(
            listing_id: $listingId,
        );
    }

    public function createDraftListing(ListingDTO $listingDTO)
    {
        try {
            return Listing::create(
                shop_id: $this->shop->shop_oauth['shop_id'],
                data: [
                    'title' => $listingDTO->title,
                    'description' => $listingDTO->description,
                    'quantity' => 999,
                    'price' => $listingDTO->price,
                    'who_made' => $listingDTO->whoMade,
                    'when_made' => $listingDTO->whenMade,
                    'taxonomy_id' => $listingDTO->taxonomyId,
                    'shipping_profile_id' => $listingDTO->shippingProfileId,
                    'return_policy_id' => $listingDTO->returnPolicyId,
                    'materials' => $listingDTO->materials,
                    'item_weight' => $listingDTO->itemWeight,
                    'item_length' => $listingDTO->itemLength,
                    'item_width' => $listingDTO->itemWidth,
                    'item_height' => $listingDTO->itemHeight,
                    'type' => 'physical',
                ],
            );
        } catch (Exception $e) {
            Log::error($e->getMessage().PHP_EOL.$e->getFile().PHP_EOL.$e->getTraceAsString());

            return null;
        }
    }

    public function updateListing(int $listingId, array $data)
    {
        return Listing::update(
            shop_id: $this->shop->shop_oauth['shop_id'],
            listing_id: $listingId,
            data: $data,
        );
    }

    public function deleteListing(int $listingId): bool
    {
        return Listing::delete(listing_id: $listingId);
    }
}
