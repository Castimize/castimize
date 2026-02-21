<?php

declare(strict_types=1);

namespace App\Services\Etsy\Resources;

use Etsy\Collection;
use Etsy\Resource;
use Etsy\Resources\ListingFile;
use Etsy\Resources\ListingImage;
use Etsy\Resources\ListingInventory;
use Etsy\Resources\ListingProduct;
use Etsy\Resources\ListingProperty;
use Etsy\Resources\ListingTranslation;
use Etsy\Resources\ListingVariationImage;
use Etsy\Resources\ListingVideo;
use Etsy\Resources\Review;
use Etsy\Resources\Transaction;

/**
 * @property int $listing_id
 * @property int $shop_id
 */
class Listing extends Resource
{
    /**
     * @var array
     */
    protected $_saveable = [
        'image_ids',
        'title',
        'description',
        'materials',
        'should_auto_renew',
        'shipping_profile_id',
        'return_policy_id',
        'shop_section_id',
        'item_weight',
        'item_length',
        'item_width',
        'item_height',
        'item_weight_unit',
        'item_dimensions_unit',
        'is_taxable',
        'taxonomy_id',
        'tags',
        'who_made',
        'when_made',
        'featured_rank',
        'is_personalizable',
        'personalization_is_required',
        'personalization_char_count_max',
        'personalization_instructions',
        'state',
        'is_supply',
        'production_partner_ids',
        'type',
    ];

    /**
     * @var array
     */
    protected $_associations = [
        'Shop' => 'Shop',
        'User' => 'User',
        'Images' => 'ListingImage',
    ];

    /**
     * Get all active listings on Etsy.
     *
     * @return Collection[Etsy\Resources\Listing]
     */
    public static function all(
        array $params = []
    ): Collection {
        return self::request(
            'GET',
            '/application/listings/active',
            'Listing',
            $params
        );
    }

    /**
     * Get all active listings on Etsy. Filtered by listing ID. Support upto 100 IDs.
     *
     * @return Collection[Etsy\Resources\Listing]
     */
    public static function allByIds(
        string|array $listing_ids,
        array $includes = []
    ): Collection {
        $params = [
            'listing_ids' => $listing_ids,
        ];
        if (count($includes) > 0) {
            $params['includes'] = $includes;
        }

        return self::request(
            'GET',
            '/application/listings/batch',
            'Listing',
            $params
        );
    }

    /**
     * Get all listings for a shop.
     *
     * @return Collection[Etsy\Resources\Listing]
     */
    public static function allByShop(
        int $shop_id,
        array $params = []
    ): Collection {
        return self::request(
            'GET',
            "/application/shops/{$shop_id}/listings",
            'Listing',
            $params
        );
    }

    /**
     * Get all active listings for a shop.
     *
     * @return Collection[Etsy\Resources\Listing]
     */
    public static function allActiveByShop(
        int $shop_id,
        array $params = []
    ): Collection {
        return self::request(
            'GET',
            "/application/shops/{$shop_id}/listings/active",
            'Listing',
            $params
        );
    }

    /**
     * Get all featured listings for a shop.
     *
     * @return Collection[Etsy\Resources\Listing]
     */
    public static function allFeaturedByShop(
        int $shop_id,
        array $params = []
    ): Collection {
        return self::request(
            'GET',
            "/application/shops/{$shop_id}/listings/featured",
            'Listing',
            $params
        );
    }

    /**
     * Get all listings from a receipt.
     *
     * @return Collection[Etsy\Resources\Listing]
     */
    public static function allByReceipt(
        int $shop_id,
        int $receipt_id,
        array $params = []
    ): Collection {
        return self::request(
            'GET',
            "/application/shops/{$shop_id}/receipts/{$receipt_id}/listings",
            'Listing',
            $params
        );
    }

    /**
     * Get all the listings within a shop return policy.
     *
     * @return Collection[\Etsy\Resources\Listing]
     */
    public static function allByReturnPolicy(
        int $shop_id,
        int $policy_id
    ): Collection {
        return self::request(
            'GET',
            "/application/shops/{$shop_id}/policies/return/{$policy_id}/listings",
            'Listing'
        );
    }

    /**
     * Get all listings withing specified shop sections.
     *
     * @return Collection[\Etsy\Resources\Listing]
     */
    public static function allByShopSections(
        int $shop_id,
        array|int $section_ids,
        array $params = []
    ): Collection {
        $params['shop_section_ids'] = $section_ids;

        return self::request(
            'GET',
            "/application/shops/{$shop_id}/shop-sections/listings",
            'Listing',
            $params
        );
    }

    /**
     * Get a listing.
     */
    public static function get(
        int $listing_id,
        array $params = []
    ) {
        return self::request(
            'GET',
            "/application/listings/{$listing_id}",
            'Listing',
            $params
        );
    }

    /**
     * Create a draft Etsy listing.
     */
    public static function create(
        int $shop_id,
        array $data
    ) {
        return self::request(
            'POST',
            "/application/shops/{$shop_id}/listings",
            'Listing',
            $data
        );
    }

    /**
     * Delete an Etsy listing.
     */
    public static function delete(
        int $listing_id
    ): bool {
        return self::deleteRequest(
            "/application/listings/{$listing_id}"
        );
    }

    /**
     * Updates an Etsy listing.
     */
    public static function update(
        int $shop_id,
        int $listing_id,
        array $data
    ) {
        return self::request(
            'PATCH',
            "/application/shops/{$shop_id}/listings/{$listing_id}",
            'Listing',
            $data
        );
    }

    /**
     * Saves updates to the current listing.
     */
    public function save(
        ?array $data = null
    ): self {
        if (! $data) {
            $data = $this->getSaveData(true);
        }
        if (count($data) == 0) {
            return $this;
        }

        return $this->updateRequest(
            "/application/shops/{$this->shop_id}/listings/{$this->listing_id}",
            $data,
            'PATCH'
        );
    }

    /**
     * Get all reviews for the listing.
     *
     * @return Collection[Etsy\Resources\Review]
     */
    public function reviews(
        array $params = []
    ): Collection {
        return Review::allByListing($this->listing_id, $params);
    }

    /**
     * Get all transactions for the listing.
     *
     * @return Collection[Etsy\Resources\Transaction]
     */
    public function transactions(
        array $params = []
    ): Collection {
        return Transaction::allByListing($this->shop_id, $this->listing_id, $params);
    }

    /**
     * Get all properties for the listing.
     *
     * @return Collection[Etsy\Resources\ListingProperty]
     */
    public function properties(): Collection
    {
        return ListingProperty::all(
            $this->shop_id,
            $this->listing_id
        );
    }

    /**
     * Get all files for the listing.
     *
     * @return Collection[Etsy\Resources\ListingFile]
     */
    public function files(): Collection
    {
        return ListingFile::all(
            $this->shop_id,
            $this->listing_id
        );
    }

    /**
     * Get a specific listing file.
     */
    public function file(
        int $file_id
    ): ?ListingFile {
        return ListingFile::get(
            $this->shop_id,
            $this->listing_id,
            $file_id
        );
    }

    /**
     * Link a file to the listing.
     */
    public function linkFile(
        int $file_id,
        int $rank = 1
    ): ?ListingFile {
        return ListingFile::create(
            $this->shop_id,
            $this->listing_id,
            [
                'listing_file_id' => $file_id,
                'rank' => 1,
            ]
        );
    }

    /**
     * Get the images for the listing.
     *
     * @return Collection[\Etsy\Resources\ListingImage]
     */
    public function images(): Collection
    {
        return ListingImage::all(
            $this->listing_id
        );
    }

    /**
     * Get a specific listing image.
     */
    public function image(
        int $image_id
    ): ?ListingImage {
        return ListingImage::get(
            $this->listing_id,
            $image_id
        );
    }

    /**
     * Link an existing image to the listing.
     */
    public function linkImage(
        int $image_id,
        array $options = []
    ): ?ListingImage {
        $options['listing_image_id'] = $image_id;

        return ListingImage::create(
            $this->shop_id,
            $this->listing_id,
            $options
        );
    }

    /**
     * Get the variation images for the listing.
     *
     * @return Collection[\Etsy\Resources\ListingVariationImage]
     */
    public function variationImages(): Collection
    {
        return ListingVariationImage::all(
            $this->shop_id,
            $this->listing_id
        );
    }

    /**
     * Get the videos for the listing.
     *
     * @return Collection[\Etsy\Resources\ListingVideo]
     */
    public function videos(): Collection
    {
        return ListingVideo::all(
            $this->listing_id
        );
    }

    /**
     * Get a specific listing image.
     */
    public function video(
        int $video_id
    ): ?ListingVideo {
        return ListingVideo::get(
            $this->listing_id,
            $video_id
        );
    }

    /**
     * Link an existing image to the listing.
     */
    public function linkVideo(
        int $video_id
    ): ?ListingVideo {
        $data['video_id'] = $video_id;

        return ListingVideo::create(
            $this->shop_id,
            $this->listing_id,
            $data
        );
    }

    /**
     * Get the listing inventory.
     */
    public function inventory(array $params = []): ?ListingInventory
    {
        return ListingInventory::get(
            $this->listing_id,
            $params
        );
    }

    /**
     * Get a listing product.
     */
    public function product(
        int $product_id
    ): ?ListingProduct {
        return ListingProduct::get(
            $this->listing_id,
            $product_id
        );
    }

    /**
     * Get a listing translation.
     */
    public function translation(
        string $language
    ): ?ListingTranslation {
        return ListingTranslation::get(
            $this->shop_id,
            $this->listing_id,
            $language
        );
    }
}
