<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PricesApiController extends ApiController
{
    /**
     * @return
     */
    public function calculatePrice(Request $request)
    {
        Log::info(print_r($request->toArray(), true));
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return new UserResource(auth()->user());
    }
}
