<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\CalculatedPriceResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PricesApiController extends ApiController
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculatePrice(Request $request)
    {
        abort_if(Gate::denies('viewPricing'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return response()->json(10.00);
    }
}
