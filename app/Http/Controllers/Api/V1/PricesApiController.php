<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CalculatedPriceResource;
use App\Models\Material;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\ModelsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PricesApiController extends ApiController
{
    /**
     * @param Request $request
     * @return CalculatedPriceResource
     */
    public function calculatePrice(Request $request): CalculatedPriceResource
    {
        //Log::info(print_r($request->all(), true));
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $price = (new CalculatePricesService())->calculatePrice($request);

        if ($request->has('file_name', 'original_file_name', 'thumb')) {
            //(new ModelsService())->storeModelFromApi($request);
        }

        return new CalculatedPriceResource($price);
    }
}
