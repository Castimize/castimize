<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\DeleteCustomerRequest;
use App\Http\Requests\ShowCustomerWpRequest;
use App\Http\Requests\ShowOrderWpRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Country;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Order;
use App\Services\Admin\CustomersService;
use App\Services\Admin\OrdersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use JsonException;
use Symfony\Component\HttpFoundation\Response;

class OrdersApiController extends ApiController
{
    /**
     * @param Order $order
     * @return OrderResource
     */
    public function show(Order $order): OrderResource
    {
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new OrderResource($order);
    }

    /**
     * @param ShowOrderWpRequest $request
     * @return OrderResource
     */
    public function showOrderWp(ShowOrderWpRequest $request): OrderResource
    {
        $order = Order::where('wp_id', $request->wp_id)->first();
        if ($order === null) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        return new OrderResource($order);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function calculateExpectedDeliveryDate(Request $request): JsonResponse
    {
        Log::info(print_r($request->all(), true));
        $country = Country::with('logisticsZone.shippingFee')->where('alpha2', $request->country)->first();
        $uploads = $request->uploads;
        $biggestCustomerLeadTime = null;
        foreach ($uploads as $upload) {
            $material = Material::where('wp_id', $upload['material_id'])->first();
            $customerLeadTime = $material->dc_lead_time + ($country->logisticsZone->shippingFee?->default_lead_time ?? 0);
            if ($biggestCustomerLeadTime === null || $customerLeadTime > $biggestCustomerLeadTime) {
                $biggestCustomerLeadTime = $customerLeadTime;
            }
        }
        $expectedDeliveryDate = now()->addBusinessDays($biggestCustomerLeadTime, 'add')->format('Y-m-d');

        return response()->json(['success' => true, 'expected_delivery_date' => $expectedDeliveryDate]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeOrderWp(Request $request): JsonResponse
    {
        Log::info(print_r($request->all(), true));
        $order = (new OrdersService())->storeOrderWpFromApi($request);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
