<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\DeleteCustomerRequest;
use App\Http\Requests\ShowCustomerWpRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\Admin\CustomersService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
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
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $customer = Customer::where('wp_id', $request->wp_id)->first();
        if ($customer === null) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        return new CustomerResource($customer);
    }

    /**
     * @param StoreCustomerRequest $request
     * @return JsonResponse
     */
    public function storeCustomerWp(StoreCustomerRequest $request): JsonResponse
    {
        $customer = (new CustomersService())->storeCustomerFromApi($request);

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @param DeleteCustomerRequest $request
     * @return Response
     */
    public function deleteCustomerWp(DeleteCustomerRequest $request): Response
    {
        $customer = Customer::where('wp_id', $request->wp_id)->first();
        if ($customer === null) {
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $customer->delete();

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
