<?php

namespace App\Virtual\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Upload",
 *     description="Upload/Line item model for orders",
 *
 *     @OA\Xml(
 *         name="Upload"
 *     )
 * )
 */
class Upload
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="Upload ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(
     *     title="model_name",
     *     description="Model name",
     *     example="Custom Part v1"
     * )
     *
     * @var string
     */
    private $model_name;

    /**
     * @OA\Property(
     *     title="file_name",
     *     description="File name",
     *     example="part_001.stl"
     * )
     *
     * @var string
     */
    private $file_name;

    /**
     * @OA\Property(
     *     title="quantity",
     *     description="Quantity",
     *     example=2
     * )
     *
     * @var int
     */
    private $quantity;

    /**
     * @OA\Property(
     *     title="material_name",
     *     description="Material name",
     *     example="PLA White"
     * )
     *
     * @var string
     */
    private $material_name;
}
