<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CalculatedPriceResource;
use App\Http\Resources\CalculatedShippingFeeResource;
use App\Services\Admin\CalculatePricesService;
use App\Services\Admin\ModelsService;
use App\Services\Shippo\ShippoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Shippo_Address;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AddressApiController extends ApiController
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function validate(Request $request): JsonResponse
    {
        Log::info(print_r($request->all(), true));
        $addressData = [
            'name' => $request->name,
            'company' => $request->company,
            'street1' => $request->address_1,
            'street2' => $request->address_2,
            'city' => $request->city,
            'state' => $request->state,
            'zip' => $request->postal_code,
            'country' => $request->country,
            'email' => $request->email,
        ];

        $response = (new ShippoService())->setFromAddress($addressData)->validateAddress();

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
