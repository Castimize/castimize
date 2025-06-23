<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Services\Admin\CustomersService;
use App\Services\Admin\PaymentService;
use App\Services\Admin\ShopOwnersService;

class PaymentsApiController extends ApiController
{
    public function __construct(
        private ShopOwnersService $shopOwnersService,
        private CustomersService $customersService,
        private PaymentService $paymentService,
    ) {
    }

    public function createSetupIntent(int $customerId)
    {
        $customer = Customer::where('wp_id', $customerId)->first();
        $setupIntent = $this->paymentService->createStripeSetupIntent($customer);

        return response()->json([
            'client_secret' => $setupIntent->client_secret,
        ]);
    }
}
