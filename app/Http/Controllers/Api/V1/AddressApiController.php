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
use Illuminate\Support\Facades\Log;
use Shippo_InvalidRequestError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AddressApiController extends ApiController
{
    public function validate(Request $request): JsonResponse
    {
        $nullIfNullString = static fn (mixed $value): mixed => ($value === 'null' || $value === '') ? null : $value;

        $addressData = [
            'name' => $request->name,
            'company' => $nullIfNullString($request->company),
            'street1' => $request->address_1,
            'street2' => $nullIfNullString($request->address_2),
            'city' => $request->city,
            'state' => $nullIfNullString($request->state),
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

        try {
            $shippingService = app(ShippingService::class);
            $response = $shippingService->setFromAddress($addressData)->validateAddress('From');
        } catch (Shippo_InvalidRequestError $e) {
            Log::warning('Shippo_InvalidRequestError during address validation', [
                'address' => $addressData,
                'error' => $e->getMessage(),
            ]);
            $response = [
                'valid' => false,
                'address' => $addressData,
                'address_changed' => false,
                'messages' => [['source' => 'SHIPPO', 'code' => 'invalid_request', 'type' => 'address_error', 'text' => $e->getMessage()]],
            ];
        }

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
