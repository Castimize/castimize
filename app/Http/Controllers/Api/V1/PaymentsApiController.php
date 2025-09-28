<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Customer;
use App\Services\Admin\LogRequestService;
use App\Services\Admin\PaymentService;
use App\Services\Admin\ShopOwnersService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PaymentsApiController extends ApiController
{
    public function __construct(
        private PaymentService $paymentService,
        private ShopOwnersService $shopOwnersService,
    ) {
    }

    public function createSetupIntent(int $customerId): JsonResponse
    {
//        dd($this->paymentService->getStripePaymentMethods());
//        dd($this->paymentService->getStripePaymentMethod('pm_1S3AQP0004dOynzNFm4eHlBU'));
//        dd($this->paymentService->getStripeMandate('mandate_1SC6lJ0004dOynzNAm8FpUfz'));
//        dd($this->paymentService->getStripeSetupIntent('seti_1SB23Q0004dOynzNEqPH5qkD'));

        $customer = Customer::where('wp_id', $customerId)->first();
        if (! $customer) {
            LogRequestService::addResponse(request(), [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }
        $setupIntent = $this->paymentService->createStripeSetupIntent($customer);

        return response()->json([
            'client_secret' => $setupIntent->client_secret,
        ]);
    }

    public function attachPaymentMethod(int $customerId, Request $request)
    {
        $customer = Customer::with('shopOwner.shops')->where('wp_id', $customerId)->first();
        if (! $customer) {
            LogRequestService::addResponse(request(), [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        try {
            $this->paymentService->attachStripePaymentMethod(
                customer: $customer,
                paymentMethodId: $request->payment_method,
            );
        } catch (Exception $exception) {
            LogRequestService::addResponse(request(), [
                'message' => $exception->getMessage() . PHP_EOL . $exception->getFile(),
            ], 500);
            abort(Response::HTTP_BAD_REQUEST, 'Unable to attach payment method with error: ' . $exception->getMessage());
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }

    public function cancelMandate(int $customerId)
    {
        $customer = Customer::with('shopOwner.shops')->where('wp_id', $customerId)->first();
        if (! $customer) {
            LogRequestService::addResponse(request(), [
                'message' => '404 Not found',
            ], 404);
            abort(Response::HTTP_NOT_FOUND, '404 Not found');
        }

        try {
            $this->paymentService->cancelStripeMandate($customer);

            $shopOwner = $this->shopOwnersService->update(
                shopOwner: $customer->shopOwner,
                data: [
                    'active' => 0,
                ],
            );

            $this->shopOwnersService->setShopsActiveState(
                shopOwner: $shopOwner,
                active: 0,
            );
            $shopOwner->refresh();

        } catch (Exception $exception) {
            LogRequestService::addResponse(request(), [
                'message' => $exception->getMessage() . PHP_EOL . $exception->getFile(),
            ], 500);
            abort(Response::HTTP_BAD_REQUEST, 'Unable to cancel mandate with error: ' . $exception->getMessage());
        }

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
