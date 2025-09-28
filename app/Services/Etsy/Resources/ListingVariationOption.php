<?php

declare(strict_types=1);

namespace App\Services\Etsy\Resources;

use Etsy\Collection;
use Etsy\Resource;

class ListingVariationOption extends Resource {
    /**
     * @var array
     */
    protected $_saveable = [
        'property_id',
        'formatted_values',
        'is_available',
        'visible',
    ];

    /**
     * @var array
     */
    protected $_associations = [
        'Listing' => 'Listing',
    ];

    /**
     * Get all listing variation options on Etsy.
     *
     * @return Collection[App\Services\Etsy\Resources\ListingVariationOption]
     */
    public static function all(
        int $listing_id,
        array $params = []
    ): Collection {
        return self::request(
            'GET',
            "/application/listings/{$listing_id}/variation_options",
            'ListingVariationOption',
            $params
        );
    }

    /**
     * Get a listing option variation.
     */
    public static function get(
        int $listing_id,
        int $variation_option_id,
        array $params = []
    ): Collection {
        return self::request(
            'GET',
            "/application/listings/{$listing_id}/variation_options/{$variation_option_id}",
            'ListingVariationOption',
            $params
        );
    }

    /**
     * Updates an Etsy listing variation option.
     */
    public static function update(
        int $listing_id,
        array $data
    ) {
        return self::request(
            'PUT',
            "/application/listings/{$listing_id}/variation_options",
            'ListingVariationOption',
            $data
        );
    }
}
