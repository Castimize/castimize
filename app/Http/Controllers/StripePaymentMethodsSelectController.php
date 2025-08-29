<?php

namespace App\Http\Controllers;

use App\Models\Rejection;
use App\Services\Payment\Stripe\StripeService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StripePaymentMethodsSelectController extends Controller
{

    public function __construct(
        private StripeService $stripeService,
    ) {}

    /**
     * Handle a private rejection image download.
     *
     * @throws ValidationException
     */
    public function __invoke(Request $request, int $id)
    {
        $paymentMethods = $this->stripeService->getPaymentMethods();
    }
}
