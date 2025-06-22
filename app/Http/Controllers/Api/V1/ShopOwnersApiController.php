<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ShopOwnerResource;
use App\Models\Customer;
use App\Services\Admin\CustomersService;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\ShopOwnersService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ShopOwnersApiController extends ApiController
{
    public function __construct(
        private ShopOwnersService $shopOwnersService,
        private CustomersService $customersService,
    ) {
    }

    /**
     * @param Customer $customer
     * @return ShopOwnerResource
     */
    public function show(Customer $customer): ShopOwnerResource
    {
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        if (! $customer->shopOwner) {
            LogRequestService::addResponse(request(), ['message' => '404 Not found'], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $response = new ShopOwnerResource($customer->shopOwner);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }

    public function store(Request $request, Customer $customer): ShopOwnerResource
    {
        if ($customer->shopOwner) {
            LogRequestService::addResponse(request(), ['message' => '400 Bad request, shop owner already exists, use the update method'], Response::HTTP_BAD_REQUEST);
            abort(Response::HTTP_BAD_REQUEST, '400 Bad request, shop owner already exists use the update method');
        }

        $shopOwner = $this->shopOwnersService->createShopOwner($customer);

        if ($request->vat_number) {
            $this->customersService->updateCustomer(
                request: $request,
                customer: $customer,
                data: [
                    'vat_number' => $request->vat_number,
                ],
            );
        }

        $response = new ShopOwnerResource($shopOwner);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }

    public function update(Request $request, Customer $customer): ShopOwnerResource
    {
        if (! $customer->shopOwner) {
            LogRequestService::addResponse(request(), ['message' => '400 Bad request, shop owner doesn\'t exist, use the store method'], Response::HTTP_BAD_REQUEST);
            abort(Response::HTTP_BAD_REQUEST, '400 Bad request, shop owner doesn\'t exist, use the store method');
        }

        $shopOwner = $customer->shopOwner;

        if ($request->vat_number) {
            $this->customersService->updateCustomer(
                request: $request,
                customer: $customer,
                data: [
                    'vat_number' => $request->vat_number,
                ],
            );
        }

        if ($request->shop) {
            $shop = $this->shopOwnersService->createShop(
                request: $request,
                shopOwner: $shopOwner,
            );
        }

        $shopOwner->refresh();
        $response = new ShopOwnerResource($shopOwner);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }
}
