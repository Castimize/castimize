<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ShopOwnerResource;
use App\Http\Resources\ShopResource;
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
    ) {}

    public function show(int $customerId): ShopOwnerResource
    {
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $customer = Customer::with('shopOwner')->where('wp_id', $customerId)->first();
        if (! $customer) {
            LogRequestService::addResponse(request(), [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        $response = new ShopOwnerResource($customer->shopOwner);
        LogRequestService::addResponse(request(), $response);

        return $response;
    }

    public function showShop(int $customerId, string $shop): ShopResource
    {
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $customer = Customer::with('shopOwner.shops')->where('wp_id', $customerId)->first();
        if (! $customer || ! $customer->shopOwner) {
            LogRequestService::addResponse(request(), [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        foreach ($customer->shopOwner->shops as $s) {
            if ($s->shop === $shop) {
                $response = new ShopResource($s);
                LogRequestService::addResponse(request(), $response);

                return $response;
            }
        }

        LogRequestService::addResponse(request(), [
            'message' => '404 Not found',
        ], 404);
        abort(Response::HTTP_NOT_FOUND, '404 Not found');
    }

    public function store(Request $request, int $customerId): ShopOwnerResource
    {
        $customer = Customer::with('shopOwner')->where('wp_id', $customerId)->first();
        if ($customer && $customer->shopOwner) {
            LogRequestService::addResponse(request(), [
                'message' => '400 Bad request, shop owner already exists, use the update method',
            ], Response::HTTP_BAD_REQUEST);
            abort(Response::HTTP_BAD_REQUEST, '400 Bad request, shop owner already exists use the update method');
        }

        $shopOwner = $this->shopOwnersService->createShopOwner($customer);

        if ($request->billing_eu_vat_number) {
            $this->customersService->updateCustomer(
                request: $request,
                customer: $customer,
                data: [
                    'vat_number' => $request->billing_eu_vat_number,
                ],
            );
        }

        $response = new ShopOwnerResource($shopOwner);
        LogRequestService::addResponse(request(), $response);

        return $response;
    }

    public function update(Request $request, int $customerId): ShopOwnerResource
    {
        $customer = Customer::with('shopOwner')->where('wp_id', $customerId)->first();
        if ($customer && ! $customer->shopOwner) {
            LogRequestService::addResponse(request(), [
                'message' => '400 Bad request, shop owner doesn\'t exist, use the store method',
            ], Response::HTTP_BAD_REQUEST);
            abort(Response::HTTP_BAD_REQUEST, '400 Bad request, shop owner doesn\'t exist, use the store method');
        }

        $shopOwner = $customer->shopOwner;

        if ($request->billing_eu_vat_number && $request->billing_eu_vat_number !== $customer->vat_numnber) {
            $this->customersService->updateCustomer(
                request: $request,
                customer: $customer,
                data: [
                    'vat_number' => $request->billing_eu_vat_number,
                ],
            );
        }

        if ($request->shop) {
            $this->shopOwnersService->createShop(
                request: $request,
                shopOwner: $shopOwner,
            );
        }

        $shopOwner->refresh();
        $response = new ShopOwnerResource($shopOwner);
        LogRequestService::addResponse(request(), $response);

        return $response;
    }

    public function updateActive(Request $request, int $customerId): ShopOwnerResource
    {
        $customer = Customer::with('shopOwner.shops')->where('wp_id', $customerId)->first();
        if ($customer && ! $customer->shopOwner) {
            LogRequestService::addResponse(request(), [
                'message' => '400 Bad request, shop owner doesn\'t exist, use the store method',
            ], Response::HTTP_BAD_REQUEST);
            abort(Response::HTTP_BAD_REQUEST, '400 Bad request, shop owner doesn\'t exist, use the store method');
        }

        $shopOwner = $this->shopOwnersService->update(
            shopOwner: $customer->shopOwner,
            data: [
                'active' => $request->active === '1' ? 1 : 0,
            ],
        );

        $this->shopOwnersService->setShopsActiveState(
            shopOwner: $shopOwner,
            active: (bool) ($request->active === '1' ? 1 : 0),
        );
        $shopOwner->refresh();

        $response = new ShopOwnerResource($shopOwner);
        LogRequestService::addResponse(request(), $response);

        return $response;
    }

    public function updateActiveShop(Request $request, int $customerId, string $shop): ShopResource
    {
        $customer = Customer::with('shopOwner.shops')->where('wp_id', $customerId)->first();
        if ($customer && ! $customer->shopOwner) {
            LogRequestService::addResponse(request(), [
                'message' => '400 Bad request, shop doesn\'t exist, use the store method',
            ], Response::HTTP_BAD_REQUEST);
            abort(Response::HTTP_BAD_REQUEST, '400 Bad request, shop doesn\'t exist, use the store method');
        }
        if (! is_array($customer->stripe_data) || ! array_key_exists('mandate_id', $customer->stripe_data)) {
            LogRequestService::addResponse(request(), [
                'message' => '400 Bad request, no mandate found',
            ], Response::HTTP_BAD_REQUEST);
            abort(Response::HTTP_BAD_REQUEST, '400 Bad request, no mandate found');
        }

        foreach ($customer->shopOwner->shops as $s) {
            if ($s->shop === $shop) {
                $s = $this->shopOwnersService->setShopActiveState(
                    shop: $s,
                    active: (bool) ($request->active === '1' ? 1 : 0),
                );

                $response = new ShopResource($s);
                LogRequestService::addResponse(request(), $response);

                return $response;
            }
        }

        LogRequestService::addResponse(request(), [
            'message' => '404 Not found',
        ], 404);
        abort(Response::HTTP_NOT_FOUND, '404 Not found');
    }
}
