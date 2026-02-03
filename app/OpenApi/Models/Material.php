<?php

namespace App\OpenApi\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="Material",
 *     description="Material model",
 *
 *     @OA\Xml(
 *         name="Material"
 *     )
 * )
 */
class Material
{
    /**
     * @OA\Property(
     *     title="id",
     *     description="Material ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $id;

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
     *     title="name",
     *     description="Material name",
     *     example="PLA White"
     * )
     *
     * @var string
     */
    private $name;
}
