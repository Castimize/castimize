<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\DeleteCustomerRequest;
use App\Http\Requests\ShowCustomerWpRequest;
use App\Http\Requests\ShowOrderWpRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\OrderResource;
use App\Models\Customer;
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
        Log::info(print_r($request->uploads, true));
        $expectedDeliveryDate = '';
        $uploads = $request->uploads;

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
