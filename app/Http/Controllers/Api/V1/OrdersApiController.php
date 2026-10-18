<?php

namespace App\Http\Controllers\Api\V1;

use App\DTO\Order\OrderDTO;
use App\Http\Requests\ShowOrderWpRequest;
use App\Http\Resources\OrderResource;
use App\Jobs\CreateOrderFromDTO;
use App\Jobs\UpdateOrderFromDTO;
use App\Models\Country;
use App\Models\Order;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\OrdersService;
use App\Traits\Api\AuthorizesCustomerAccess;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OrdersApiController extends ApiController
{
    use AuthorizesCustomerAccess;

    public function __construct(
        private OrdersService $ordersService,
    ) {}

    public function show(int $orderNumber): OrderResource
    {
        $order = Order::with('customer')->where('order_number', $orderNumber)->first();
        if ($order === null) {
            LogRequestService::addResponse(request(), [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $this->authorizeCustomerAccess($order->customer?->wp_id ?? -1, 'viewOrder');

        $response = new OrderResource($order);
        LogRequestService::addResponse(request(), $response);

        return $response;
    }

    public function showOrderWp(ShowOrderWpRequest $request)
    {
        $order = \Codexshaper\WooCommerce\Facades\Order::find($request->wp_id);
        //        $order = Order::where('wp_id', $request->wp_id)->first();
        if ($order === null) {
            LogRequestService::addResponse($request, [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        LogRequestService::addResponse($request, $order);

        return $order;
    }

    public function showWpOrder(int $orderNumber): JsonResponse
    {
        $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($orderNumber);
        $response = $wpOrder;
        LogRequestService::addResponse(request(), $response->toArray());

        return response()
            ->json($response)
            ->setStatusCode(Response::HTTP_OK);
    }

    public function calculateExpectedDeliveryDate(Request $request): JsonResponse
    {
        $country = Country::with('logisticsZone.shippingFee')->where('alpha2', $request->country)->first();
        $uploads = $this->normalizeUploads($request->uploads);

        $expectedDeliveryDate = $this->ordersService->calculateExpectedDeliveryDate($uploads, $country);

        $response = [
            'success' => true,
            'expected_delivery_date' => $expectedDeliveryDate,
        ];
        LogRequestService::addResponse($request, $response);

        return response()->json($response);
    }

    public function storeOrderWp(Request $request): JsonResponse
    {
        $logRequestId = null;
        if ($request->has('log_request_id')) {
            $logRequestId = $request->log_request_id;
        }

        CreateOrderFromDTO::dispatch(OrderDto::fromWpRequest($request), $logRequestId);

        $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($request->id);
        $response = $wpOrder;
        LogRequestService::addResponse($request, $response->toArray());

        return response()
            ->json($response)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function updateOrderWp(Request $request): JsonResponse
    {
        $order = Order::where('wp_id', $request->id)->first();
        $logRequestId = null;
        if ($request->has('log_request_id')) {
            $logRequestId = $request->log_request_id;
        }
        if ($order === null) {
            CreateOrderFromDTO::dispatch(OrderDto::fromWpRequest($request), $logRequestId);
        } else {
            UpdateOrderFromDTO::dispatch(OrderDto::fromWpRequest($request), $logRequestId);
        }

        $wpOrder = \Codexshaper\WooCommerce\Facades\Order::find($request->id);
        $response = $wpOrder;
        LogRequestService::addResponse($request, $response->toArray());

        return response()
            ->json($response)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Accepts uploads as an array or JSON string, where each upload may itself be
     * an array or a JSON string. Entries without a material_id are dropped.
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeUploads(mixed $uploads): array
    {
        if (is_string($uploads)) {
            $uploads = json_decode($uploads, true, 512, JSON_THROW_ON_ERROR);
        }

        $normalized = [];
        foreach ((array) $uploads as $upload) {
            if (is_string($upload)) {
                $upload = json_decode($upload, true);
            }

            if (is_array($upload) && isset($upload['material_id'])) {
                $normalized[] = $upload;
            }
        }

        return $normalized;
    }
}
