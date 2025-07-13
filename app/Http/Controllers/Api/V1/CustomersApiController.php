<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\DeleteCustomerRequest;
use App\Http\Requests\ShowCustomerWpRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\Admin\CustomersService;
use App\Services\Admin\LogRequestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class CustomersApiController extends ApiController
{
    public function __construct(
        private CustomersService $customersService,
    ) {
    }

    public function show(Customer $customer): CustomerResource
    {
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $response = new CustomerResource($customer);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }

    public function showCustomerWp(ShowCustomerWpRequest $request): CustomerResource
    {
        $customer = Customer::where('wp_id', $request->wp_id)->first();
        if ($customer === null) {
            LogRequestService::addResponse($request, ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $response = new CustomerResource($customer);
        LogRequestService::addResponse($request, $response);
        return $response;
    }

    public function storeCustomerWp(Request $request): JsonResponse
    {
        $wpCustomer = \Codexshaper\WooCommerce\Facades\Customer::find($request->id);
        $customer = $this->customersService->storeCustomerFromWpCustomer($wpCustomer);

        $response = new CustomerResource($customer);
        LogRequestService::addResponse($request, $response);
        return $response->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function updateCustomerWp(Request $request): JsonResponse
    {
        return response()->json($request->all());
//        $customer = (new CustomersService())->storeCustomerFromApi($request);
//
//        return (new CustomerResource($customer))
//            ->response()
//            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function deleteCustomerWp(Request $request): Response
    {
        $customer = Customer::where('wp_id', $request->id)->first();
        if ($customer === null) {
            LogRequestService::addResponse($request, ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $customer->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
