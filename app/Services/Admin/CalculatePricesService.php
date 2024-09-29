<?php

namespace App\Services\Admin;

use App\Models\Country;
use App\Models\ManufacturerCost;
use App\Models\Material;
use App\Models\Price;
use App\Models\ShippingFee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

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

        if (
            $request->x_dim < $material->minimum_x_length ||
            $request->x_dim > $material->maximum_x_length ||
            $request->y_dim < $material->minimum_y_length ||
            $request->y_dim > $material->maximum_y_length ||
            $request->z_dim < $material->minimum_z_length ||
            $request->z_dim > $material->maximum_z_length ||
            $request->material_volume < $material->minimum_volume ||
            $request->material_volume > $material->maximum_volume ||
            $request->box_volume < $material->minimum_box_volume ||
            $request->box_volume > $material->maximum_box_volume
        ) {
            throw new UnprocessableEntityHttpException(__('Unable to process upload, please adjust the size of your design'));
        }

        if ((Str::contains($material->name, 'interlocking') && $request->model_parts > 6) || $request->model_parts > 2) {
            throw new UnprocessableEntityHttpException(__('Unable to process upload, please reduce the number of parts in your design'));
        }

        /**
         * $price Price
         */
        $price = $material->prices->first();
        $price->calculated_total = $this->calculatePriceOfModel($price, $request->material_volume, $request->surface_area);
        return $price;
    }

    /**
     * @param Price $price
     * @param float $materialVolume
     * @param float $surfaceArea
     * @return float
     */
    public function calculatePriceOfModel(Price $price, float $materialVolume, float $surfaceArea): float
    {
        if ($price->setup_fee) {
            $total = $price->setup_fee_amount + ($materialVolume * $price->price_volume_cc);
        } else {
            $total = ($materialVolume * $price->price_volume_cc) + ($surfaceArea * $price->price_surface_cm2);
            if ($total < $price->price_minimum_per_stl) {
                $total = $price->price_minimum_per_stl;
            }
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

    /**
     * @param Request $request
     * @return ShippingFee
     */
    public function calculateShippingFee(Request $request): ShippingFee
    {
        $country = Country::with(['logisticsZone.shippingFee'])->where('alpha2', $request->country)->first();
        if ($country === null || $country->logisticsZone === null) {
            throw new NotFoundHttpException(__('404 not found'));
        }
        $shippingFee = $country->logisticsZone->shippingFee;
        $shippingFee->calculated_total = $shippingFee->default_rate;
        $totalVolume = $this->getTotalVolumeOfUploads($request->uploads);
        if ($totalVolume > $shippingFee->cc_threshold_1) {
            $shippingFee->calculated_total += ($shippingFee->rate_increase_1 / 100) * $shippingFee->default_rate;
        }
        if ($totalVolume > $shippingFee->cc_threshold_2) {
            $shippingFee->calculated_total += ($shippingFee->rate_increase_2 / 100) * $shippingFee->default_rate;
        }
        if ($totalVolume > $shippingFee->cc_threshold_3) {
            $shippingFee->calculated_total += ($shippingFee->rate_increase_3 / 100) * $shippingFee->default_rate;
        }

        return $shippingFee;
    }

    /**
     * @param array $uploads
     * @return float|int|null
     */
    private function getTotalVolumeOfUploads(array $uploads): float|int|null
    {
        $totalVolume = 0.00;
        foreach ($uploads as $upload) {
            $totalVolume += $upload['3dp_options']['model_stats_raw']['model']['box_volume'] * $upload['quantity'];
        }
        return $totalVolume;
    }
}
