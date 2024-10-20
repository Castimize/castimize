<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CalculatedShippingFeeResource;
use App\Services\Admin\ShippingService;
use App\Services\Admin\CalculatePricesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AddressApiController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
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
//        Log::info(print_r($addressData, true));

        if (empty($addressData['country'])) {
            return response()->json(['valid' => false, 'address' => [], 'address_changed' => 0, 'messages' => []]);
        }

        $shippingService = app(ShippingService::class);
        $response = $shippingService->setFromAddress($addressData)->validateAddress('From');
//        Log::info(print_r($response, true));

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse|CalculatedShippingFeeResource
     */
    public function calculateShipping(Request $request): JsonResponse|CalculatedShippingFeeResource
    {
//        Log::info(print_r($request->all(), true));
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        try {
            $shippingFee = (new CalculatePricesService())->calculateShippingFee($request);
        } catch (UnprocessableEntityHttpException $e) {
            return response()->json([
                'errors' => $e->getMessage(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new CalculatedShippingFeeResource($shippingFee);
    }
}
