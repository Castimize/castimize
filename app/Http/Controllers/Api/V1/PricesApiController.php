<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CalculatedPriceResource;
use App\Http\Resources\CalculatedShippingFeeResource;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\LogRequestService;
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
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $price = (new CalculatePricesService())->calculatePrice($request);
        } catch (UnprocessableEntityHttpException $e) {
            LogRequestService::addResponse($request, ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], $e->getCode());
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->has('file_name', 'original_file_name') && $request->get('file_name') !== null && $request->get('original_file_name') !== null) {
            (new ModelsService())->storeModelFromApi($request);
        }

        $response = new CalculatedPriceResource($price);
        LogRequestService::addResponse($request, $response);
        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse|CalculatedShippingFeeResource
     */
    public function calculateShipping(Request $request): JsonResponse|CalculatedShippingFeeResource
    {
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $shippingFee = (new CalculatePricesService())->calculateShippingFee($request);
        } catch (UnprocessableEntityHttpException $e) {
            LogRequestService::addResponse($request, ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], $e->getCode());
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $response = new CalculatedShippingFeeResource($shippingFee);
        LogRequestService::addResponse($request, $response);
        return $response;
    }
}
