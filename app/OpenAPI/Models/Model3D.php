<?php

namespace App\OpenApi\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Model3D",
 *     description="3D Model model",
 *
 *     @OA\Xml(
 *         name="Model3D"
 *     )
 * )
 */
class Model3D
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="Model ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="customer_id",
     *     description="Customer ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $customer_id;

    /**
     * @OA\Property(
     *     title="is_shop_owner",
     *     description="Whether the customer is a shop owner",
     *     example=0
     * )
     *
     * @var int
     */
    private $is_shop_owner;

    /**
     * @OA\Property(
     *     title="shop_listing_id",
     *     description="Shop listing ID if linked to a shop",
     *     format="int64",
     *     example=null
     * )
     *
     * @var int|null
     */
    private $shop_listing_id;

    /**
     * @OA\Property(
     *     title="materials",
     *     description="Associated materials",
     *     type="array",
     *
     *     @OA\Items(ref="#/components/schemas/Material")
     * )
     *
     * @var array
     */
    private $materials;

    /**
     * @OA\Property(
     *     title="display_model_name",
     *     description="Display model name",
     *     example="Custom Part v1"
     * )
     *
     * @var string
     */
    private $display_model_name;

    /**
     * @OA\Property(
     *     title="model_name",
     *     description="Custom model name",
     *     example="Custom Part v1"
     * )
     *
     * @var string|null
     */
    private $model_name;

    /**
     * @OA\Property(
     *     title="name",
     *     description="Original file name",
     *     example="part_001.stl"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     title="file_name",
     *     description="Stored file name",
     *     example="wp-content/uploads/p3d/part_001.stl"
     * )
     *
     * @var string
     */
    private $file_name;

    /**
     * @OA\Property(
     *     title="raw_file_name",
     *     description="Raw file name without path",
     *     example="part_001.stl"
     * )
     *
     * @var string
     */
    private $raw_file_name;

    /**
     * @OA\Property(
     *     title="file_url",
     *     description="Full URL to the file",
     *     example="https://storage.example.com/wp-content/uploads/p3d/part_001.stl"
     * )
     *
     * @var string
     */
    private $file_url;

    /**
     * @OA\Property(
     *     title="file_url_site",
     *     description="Site URL to the file",
     *     example="https://example.com/wp-content/uploads/p3d/part_001.stl"
     * )
     *
     * @var string
     */
    private $file_url_site;

    /**
     * @OA\Property(
     *     title="file_thumbnail",
     *     description="Thumbnail URL",
     *     example="https://storage.example.com/wp-content/uploads/p3d/part_001.thumb.png"
     * )
     *
     * @var string
     */
    private $file_thumbnail;

    /**
     * @OA\Property(
     *     title="thumbnail_key",
     *     description="Thumbnail cache key",
     *     example="31001mm"
     * )
     *
     * @var string
     */
    private $thumbnail_key;

    /**
     * @OA\Property(
     *     title="model_volume_cc",
     *     description="Model volume in cubic centimeters",
     *     format="float",
     *     example=12.5
     * )
     *
     * @var float
     */
    private $model_volume_cc;

    /**
     * @OA\Property(
     *     title="model_volume_cc_display",
     *     description="Model volume display string",
     *     example="12.5cm3"
     * )
     *
     * @var string
     */
    private $model_volume_cc_display;

    /**
     * @OA\Property(
     *     title="model_x_length",
     *     description="Model X dimension in mm",
     *     format="float",
     *     example=50.0
     * )
     *
     * @var float
     */
    private $model_x_length;

    /**
     * @OA\Property(
     *     title="model_y_length",
     *     description="Model Y dimension in mm",
     *     format="float",
     *     example=30.0
     * )
     *
     * @var float
     */
    private $model_y_length;

    /**
     * @OA\Property(
     *     title="model_z_length",
     *     description="Model Z dimension in mm",
     *     format="float",
     *     example=20.0
     * )
     *
     * @var float
     */
    private $model_z_length;

    /**
     * @OA\Property(
     *     title="model_surface_area_cm2",
     *     description="Model surface area in square centimeters",
     *     format="float",
     *     example=85.5
     * )
     *
     * @var float
     */
    private $model_surface_area_cm2;

    /**
     * @OA\Property(
     *     title="model_surface_area_cm2_display",
     *     description="Model surface area display string",
     *     example="85.5cm3"
     * )
     *
     * @var string
     */
    private $model_surface_area_cm2_display;

    /**
     * @OA\Property(
     *     title="model_parts",
     *     description="Number of model parts",
     *     example=1
     * )
     *
     * @var int
     */
    private $model_parts;

    /**
     * @OA\Property(
     *     title="model_box_volume",
     *     description="Model bounding box volume",
     *     format="float",
     *     example=30000.0
     * )
     *
     * @var float
     */
    private $model_box_volume;

    /**
     * @OA\Property(
     *     title="model_scale",
     *     description="Model scale factor",
     *     format="float",
     *     example=1.0
     * )
     *
     * @var float
     */
    private $model_scale;

    /**
     * @OA\Property(
     *     title="price",
     *     description="Calculated price for the model",
     *     format="float",
     *     example=25.99
     * )
     *
     * @var float|null
     */
    private $price;

    /**
     * @OA\Property(
     *     title="categories_json",
     *     description="Categories in JSON format",
     *     type="array",
     *
     *     @OA\Items(
     *         type="object",
     *
     *         @OA\Property(property="category", type="string", example="Automotive")
     *     )
     * )
     *
     * @var array|null
     */
    private $categories_json;

    /**
     * @OA\Property(
     *     title="categories",
     *     description="Categories as comma-separated string",
     *     example="Automotive,Industrial"
     * )
     *
     * @var string
     */
    private $categories;

    /**
     * @OA\Property(
     *     title="meta_data",
     *     description="Additional meta data",
     *     type="object"
     * )
     *
     * @var object|null
     */
    private $meta_data;
}
