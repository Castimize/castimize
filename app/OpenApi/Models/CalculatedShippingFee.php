<?php

namespace App\OpenApi\Models;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     title="CalculatedShippingFee",
 *     description="Calculated shipping fee model",
 *
 *     @OA\Xml(
 *         name="CalculatedShippingFee"
 *     )
 * )
 */
class CalculatedShippingFee
{
    /**
     * @OA\Property(
     *     title="logistics_zone_id",
     *     description="Logistics zone ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $logistics_zone_id;

    /**
     * @OA\Property(
     *     title="currency_id",
     *     description="Currency ID",
     *     format="int64",
     *     example=1
     * )
     *
     * @var int
     */
    private $currency_id;

    /**
     * @OA\Property(
     *     title="name",
     *     description="Shipping fee name",
     *     example="Standard Shipping"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     title="default_rate",
     *     description="Default shipping rate",
     *     format="float",
     *     example=10.00
     * )
     *
     * @var float
     */
    private $default_rate;

    /**
     * @OA\Property(
     *     title="currency_code",
     *     description="Currency code",
     *     example="EUR"
     * )
     *
     * @var string
     */
    private $currency_code;

    /**
     * @OA\Property(
     *     title="default_lead_time",
     *     description="Default lead time in days",
     *     example=7
     * )
     *
     * @var int
     */
    private $default_lead_time;

    /**
     * @OA\Property(
     *     title="cc_threshold_1",
     *     description="Volume threshold 1 in cc",
     *     format="float",
     *     example=100.0
     * )
     *
     * @var float
     */
    private $cc_threshold_1;

    /**
     * @OA\Property(
     *     title="rate_increase_1",
     *     description="Rate increase for threshold 1",
     *     format="float",
     *     example=5.00
     * )
     *
     * @var float
     */
    private $rate_increase_1;

    /**
     * @OA\Property(
     *     title="cc_threshold_2",
     *     description="Volume threshold 2 in cc",
     *     format="float",
     *     example=500.0
     * )
     *
     * @var float
     */
    private $cc_threshold_2;

    /**
     * @OA\Property(
     *     title="rate_increase_2",
     *     description="Rate increase for threshold 2",
     *     format="float",
     *     example=10.00
     * )
     *
     * @var float
     */
    private $rate_increase_2;

    /**
     * @OA\Property(
     *     title="cc_threshold_3",
     *     description="Volume threshold 3 in cc",
     *     format="float",
     *     example=1000.0
     * )
     *
     * @var float
     */
    private $cc_threshold_3;

    /**
     * @OA\Property(
     *     title="rate_increase_3",
     *     description="Rate increase for threshold 3",
     *     format="float",
     *     example=15.00
     * )
     *
     * @var float
     */
    private $rate_increase_3;

    /**
     * @OA\Property(
     *     title="calculated_total_raw",
     *     description="Calculated total in base currency",
     *     format="float",
     *     example=15.00
     * )
     *
     * @var float
     */
    private $calculated_total_raw;

    /**
     * @OA\Property(
     *     title="calculated_total",
     *     description="Calculated total in requested currency",
     *     format="float",
     *     example=15.00
     * )
     *
     * @var float
     */
    private $calculated_total;
}
