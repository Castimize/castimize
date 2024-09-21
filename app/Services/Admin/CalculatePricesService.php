<?php

namespace App\Services\Admin;

use App\Models\ManufacturerCost;
use App\Models\Price;

class CalculatePricesService
{
    /**
     * @param Price $price
     * @param float $materialVolume
     * @param float $surfaceArea
     * @return float
     */
    public static function calculatePriceOfModel(Price $price, float $materialVolume, float $surfaceArea): float
    {
        if ($price->setup_fee) {
            $total = $price->setup_fee_amount + ($materialVolume * $price->price_volume_cc);
        } else if ($materialVolume <= $price->minimum_per_stl) {
            $total = $price->price_minimum_per_stl;
        } else {
            $total = ($materialVolume * $price->price_volume_cc) + ($surfaceArea * $price->price_surface_cm2);
        }

        return (float)$total;
    }

    /**
     * @param ManufacturerCost $cost
     * @param float $materialVolume
     * @param float $surfaceArea
     * @return float
     */
    public static function calculateCostsOfModel(ManufacturerCost $cost, float $materialVolume, float $surfaceArea): float
    {
        if ($cost->setup_fee) {
            $total = $cost->setup_fee_amount + ($materialVolume * $cost->costs_volume_cc);
        } else if ($materialVolume <= $cost->minimum_per_stl) {
            $total = $cost->costs_minimum_per_stl;
        } else {
            $total = ($materialVolume * $cost->costs_volume_cc) + ($surfaceArea * $cost->costs_surface_cm2);
        }

        return (float)$total;
    }
}
