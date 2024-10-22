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
use App\Services\Admin\LogRequestService;
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

        $response = new OrderResource($order);
        LogRequestService::addResponse(request(), $response);
        return $response;
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
        $response = new OrderResource($order);
        LogRequestService::addResponse($request, $response);
        return $response;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws JsonException
     */
    public function calculateExpectedDeliveryDate(Request $request): JsonResponse
    {
        $country = Country::with('logisticsZone.shippingFee')->where('alpha2', $request->country)->first();
        $uploads = $request->uploads;
        if (is_string($uploads)) {
            $uploads = json_decode($uploads, true, 512, JSON_THROW_ON_ERROR);
        }

        $biggestCustomerLeadTime = null;
        foreach ($uploads as $upload) {
            $material = Material::where('wp_id', $upload['material_id'])->first();
            $customerLeadTime = $material->dc_lead_time + ($country->logisticsZone->shippingFee?->default_lead_time ?? 0);
            if ($biggestCustomerLeadTime === null || $customerLeadTime > $biggestCustomerLeadTime) {
                $biggestCustomerLeadTime = $customerLeadTime;
            }
        }
        $expectedDeliveryDate = now()->addBusinessDays($biggestCustomerLeadTime, 'add')->toFormattedDateString();

        $response = ['success' => true, 'expected_delivery_date' => $expectedDeliveryDate];
        LogRequestService::addResponse($request, $response);
        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeOrderWp(Request $request): JsonResponse
    {
        $order = (new OrdersService())->storeOrderWpFromApi($request);

        $response = new OrderResource($order);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateOrderWp(Request $request): JsonResponse
    {
        $order = Order::where('wp_id', $request->id)->first();
        if ($order === null) {
            $order = (new OrdersService())->storeOrderWpFromApi($request);
        }

        $response = new OrderResource($order);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }
}
