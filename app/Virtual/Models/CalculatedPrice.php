<?php

namespace App\Virtual\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="CalculatedPrice",
 *     description="Calculated price model",
 *
 *     @OA\Xml(
 *         name="CalculatedPrice"
 *     )
 * )
 */
class CalculatedPrice
{
    /**
     * @OA\Property(
     *     title="currency",
     *     description="Currency code",
     *     example="EUR"
     * )
     *
     * @var string
     */
    private $currency;

    /**
     * @OA\Property(
     *     title="printer_id",
     *     description="Printer ID",
     *     format="int64",
     *     example=null
     * )
     *
     * @var int|null
     */
    private $printer_id;

    /**
     * @OA\Property(
     *     title="wp_id",
     *     description="WordPress material ID",
     *     format="int64",
     *     example=123
     * )
     *
     * @var int
     */
    private $wp_id;

    /**
     * @OA\Property(
     *     title="coating_id",
     *     description="Coating ID",
     *     format="int64",
     *     example=null
     * )
     *
     * @var int|null
     */
    private $coating_id;

    /**
     * @OA\Property(
     *     title="material_volume",
     *     description="Material volume in cc",
     *     format="float",
     *     example=12.5
     * )
     *
     * @var float
     */
    private $material_volume;

    /**
     * @OA\Property(
     *     title="support_volume",
     *     description="Support material volume in cc",
     *     format="float",
     *     example=2.5
     * )
     *
     * @var float
     */
    private $support_volume;

    /**
     * @OA\Property(
     *     title="print_time",
     *     description="Estimated print time",
     *     example=null
     * )
     *
     * @var mixed
     */
    private $print_time;

    /**
     * @OA\Property(
     *     title="box_volume",
     *     description="Bounding box volume",
     *     format="float",
     *     example=30000.0
     * )
     *
     * @var float
     */
    private $box_volume;

    /**
     * @OA\Property(
     *     title="surface_area",
     *     description="Surface area in cm2",
     *     format="float",
     *     example=85.5
     * )
     *
     * @var float
     */
    private $surface_area;

    /**
     * @OA\Property(
     *     title="scale",
     *     description="Scale factor",
     *     format="float",
     *     example=1.0
     * )
     *
     * @var float
     */
    private $scale;

    /**
     * @OA\Property(
     *     title="weight",
     *     description="Weight in grams",
     *     format="float",
     *     example=25.0
     * )
     *
     * @var float
     */
    private $weight;

    /**
     * @OA\Property(
     *     title="x_dim",
     *     description="X dimension in mm",
     *     format="float",
     *     example=50.0
     * )
     *
     * @var float
     */
    private $x_dim;

    /**
     * @OA\Property(
     *     title="y_dim",
     *     description="Y dimension in mm",
     *     format="float",
     *     example=30.0
     * )
     *
     * @var float
     */
    private $y_dim;

    /**
     * @OA\Property(
     *     title="z_dim",
     *     description="Z dimension in mm",
     *     format="float",
     *     example=20.0
     * )
     *
     * @var float
     */
    private $z_dim;

    /**
     * @OA\Property(
     *     title="polygons",
     *     description="Number of polygons",
     *     example=null
     * )
     *
     * @var int|null
     */
    private $polygons;

    /**
     * @OA\Property(
     *     title="quantity",
     *     description="Quantity",
     *     example=1
     * )
     *
     * @var int
     */
    private $quantity;

    /**
     * @OA\Property(
     *     title="original_file_name",
     *     description="Original file name",
     *     example="part_001.stl"
     * )
     *
     * @var string
     */
    private $original_file_name;

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
     *     title="thumb",
     *     description="Thumbnail URL",
     *     example=null
     * )
     *
     * @var string|null
     */
    private $thumb;

    /**
     * @OA\Property(
     *     title="total",
     *     description="Calculated total price",
     *     format="float",
     *     example=25.99
     * )
     *
     * @var float
     */
    private $total;
}
