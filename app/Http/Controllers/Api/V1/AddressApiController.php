<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Order\CalculateShippingFeeUploadDTO;
use App\Http\Resources\CalculatedShippingFeeResource;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\ShippingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AddressApiController extends ApiController
{
    public function validate(Request $request): JsonResponse
    {
        $addressData = [
            'name' => $request->name,
            'company' => $request->company ?? null,
            'street1' => $request->address_1,
            'street2' => $request->address_2 ?? null,
            'city' => $request->city,
            'state' => $request->state ?? null,
            'zip' => $request->postal_code,
            'country' => $request->country,
            'email' => $request->email,
        ];

        if (empty($addressData['country'])) {
            $response = [
                'valid' => false,
                'address' => [],
                'address_changed' => 0,
                'messages' => [],
            ];
            LogRequestService::addResponse($request, $response);

            return response()->json($response);
        }

        $shippingService = app(ShippingService::class);
        $response = $shippingService->setFromAddress($addressData)->validateAddress('From');
        LogRequestService::addResponse($request, $response);

        return response()->json($response);
    }

    public function calculateShipping(Request $request): JsonResponse|CalculatedShippingFeeResource
    {
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $shippingFee = (new CalculatePricesService)->calculateShippingFeeNew(
                countryIso: $request->country,
                uploads: collect($request->uploads)->map(fn ($upload) => CalculateShippingFeeUploadDTO::fromWpRequest($upload)),
            );
        } catch (UnprocessableEntityHttpException $e) {
            LogRequestService::addResponse($request, [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], $e->getCode());

            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $response = new CalculatedShippingFeeResource($shippingFee);
        LogRequestService::addResponse($request, $response);

        return $response;
    }
}
