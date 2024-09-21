<?php

namespace App\Services\Admin;

use App\Models\ManufacturerCost;
use App\Models\Material;
use App\Models\Price;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CalculatePricesService
{
    /**
     * @param Request $request
     * @return Price
     */
    public function calculatePrice(Request $request): Price
    {
        $material = Material::with(['prices'])->where('wp_id', $request->wp_id)->first();
        if ($material === null || $material->prices->count() === 0) {
            throw new NotFoundHttpException(__('404 Material not found'));
        }
        /**
         * $price Price
         */
        $price = $material->prices->first();
        $price->calculated_total = $this->calculatePriceOfModel($price, $request->material_volume, $request->surface_area);
        return $price;
    }

    /**
     * @param $price
     * @param float $materialVolume
     * @param float $surfaceArea
     * @return float
     */
    public function calculatePriceOfModel($price, float $materialVolume, float $surfaceArea): float
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
    public function calculateCostsOfModel(ManufacturerCost $cost, float $materialVolume, float $surfaceArea): float
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
