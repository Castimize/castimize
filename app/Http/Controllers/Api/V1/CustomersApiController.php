<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\DeleteCustomerRequest;
use App\Http\Requests\ShowCustomerWpRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\Admin\CustomersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CustomersApiController extends ApiController
{
    /**
     * @param Customer $customer
     * @return CustomerResource
     */
    public function show(Customer $customer): CustomerResource
    {
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new CustomerResource($customer);
    }

    /**
     * @param ShowCustomerWpRequest $request
     * @return CustomerResource
     */
    public function showCustomerWp(ShowCustomerWpRequest $request): CustomerResource
    {
        $customer = Customer::where('wp_id', $request->wp_id)->first();
        if ($customer === null) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        return new CustomerResource($customer);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function storeCustomerWp(Request $request): JsonResponse
    {
        Log::info(print_r($request->all(), true));
//        return response()->json($request->all());
        $customer = (new CustomersService())->storeCustomerFromApi($request);

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateCustomerWp(Request $request): JsonResponse
    {
        Log::info(print_r($request->all(), true));
        return response()->json($request->all());
//        $customer = (new CustomersService())->storeCustomerFromApi($request);
//
//        return (new CustomerResource($customer))
//            ->response()
//            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function deleteCustomerWp(Request $request): Response
    {
        $customer = Customer::where('wp_id', $request->id)->first();
        if ($customer === null) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $customer->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
