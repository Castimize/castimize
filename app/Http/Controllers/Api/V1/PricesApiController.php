<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CalculatedPriceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PricesApiController extends ApiController
{
    /**
     * @param Request $request
     * @return mixed[]
     */
    public function calculatePrice(Request $request)
    {
        Log::info(print_r($request->all(), true));
//        [printer_id] => 1
//        [material_id] => 2
//        [coating_id] =>
//        [material_volume] => 0.0043895747874172
//        [support_volume] => 0
//        [print_time] => 0
//        [box_volume] => 0.008
//        [surface_area] => 0.52148147653651
//        [x_dim] => 0.2
//        [y_dim] => 0.2
//        [z_dim] => 0.2
//        [polygons] => 2112
//        [original_file_name] => Menger_sponge_sample.stl
//        [file_name] => 66ee8d7f8f531_c15b79fffc021e57ae2659609053eba0.stl
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return (new CalculatedPriceResource(null))->toArray($request);
    }
}
