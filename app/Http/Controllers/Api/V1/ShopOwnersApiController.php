<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ShopOwnerResource;
use App\Models\ShopOwner;
use App\Services\Admin\CustomersService;
use App\Services\Admin\LogRequestService;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class ShopOwnersApiController extends ApiController
{
    public function __construct(
        protected CustomersService $customersService,
    ) {
    }

    public function show(ShopOwner $shopOwner): ShopOwnerResource
    {
        abort_if(Gate::denies('viewCustomer'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $response = new ShopOwnerResource($shopOwner);
        LogRequestService::addResponse(request(), $response);
        return $response;
    }
}
