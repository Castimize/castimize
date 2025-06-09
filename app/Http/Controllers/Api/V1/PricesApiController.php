<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Order\CalculateShippingFeeUploadDTO;
use App\Http\Resources\CalculatedPriceResource;
use App\Http\Resources\CalculatedShippingFeeResource;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\ModelsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class PricesApiController extends ApiController
{
    public function __construct(
        private ModelsService $modelsService,
        private CalculatePricesService $calculatePricesService,
    ) {
    }

    public function calculatePrice(Request $request): JsonResponse|CalculatedPriceResource
    {
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $price = $this->calculatePricesService->calculatePrice($request);
        } catch (UnprocessableEntityHttpException $e) {
            LogRequestService::addResponse($request, ['message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], $e->getCode());
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($request->has('file_name', 'original_file_name') && $request->get('file_name') !== null && $request->get('original_file_name') !== null) {
            $this->modelsService->storeModelFromApi($request);
        }

        $response = new CalculatedPriceResource($price);
        LogRequestService::addResponse($request, $response);
        return $response;
    }

    public function calculateShipping(Request $request): JsonResponse|CalculatedShippingFeeResource
    {
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $shippingFee = $this->calculatePricesService->calculateShippingFeeNew(
                countryIso: $request->country,
                uploads: collect($request->uploads)->map(fn ($upload) => CalculateShippingFeeUploadDTO::fromWpRequest($upload)),
            );
        } catch (UnprocessableEntityHttpException $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new CalculatedShippingFeeResource($shippingFee);
//        LogRequestService::addResponse($request, $response);
//        return $response;
    }
}
