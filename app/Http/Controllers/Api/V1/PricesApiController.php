<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CalculatedPriceResource;
use App\Models\Material;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\ModelsService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PricesApiController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse|CalculatedPriceResource
     */
    public function calculatePrice(Request $request): JsonResponse|CalculatedPriceResource
    {
        //Log::info(print_r($request->all(), true));
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $price = (new CalculatePricesService())->calculatePrice($request);
        } catch (UnprocessableEntityHttpException $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->has('file_name', 'original_file_name') && $request->get('file_name') !== null && $request->get('original_file_name') !== null) {
            (new ModelsService())->storeModelFromApi($request);
        }

        return new CalculatedPriceResource($price);
    }
}
