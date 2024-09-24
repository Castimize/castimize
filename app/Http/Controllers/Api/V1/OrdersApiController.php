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

    public function testIncomingOrder()
    {
        $payload = @file_get_contents('php://input');
        $payload = json_decode( $payload, true);
        Log::info(json_encode( $payload));
        return response()->json([ 'data' => $payload, 'status' => Response::HTTP_OK]);
    }

    /**
     * @param Request $request
     */
    public function storeOrderWp(Request $request)
    {
        $order = (new OrdersService())->storeOrderWpFromApi($request);

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function orderPaidCallback(Request $request)
    {
        Log::info(print_r($request->all(), true));
    }
}
