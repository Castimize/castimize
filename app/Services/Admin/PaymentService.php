<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Customer;
use App\Services\Payment\Stripe\StripeService;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\SetupIntent;

class PaymentService
{
    public function __construct(
        private StripeService $stripeService,
    ) {
    }

    public function createStripeSetupIntent(Customer $customer): SetupIntent
    {
        if ($customer->stripe_data === null || ! array_key_exists('stripe_id', $customer->stripe_data)) {
            $stripeCustomer = $this->stripeService->getCustomers(params: ['email' => $customer->email])->first();
            if (! $stripeCustomer) {
                $stripeCustomer = $this->stripeService->createCustomer(customer: $customer);
            }
            $stripeData = $customer->stripe_data ?? [];
            $stripeData['stripe_id'] = $stripeCustomer->id;
            $customer->stripe_data = $stripeData;
            $customer->save();
        }
        $setupIntent = $this->stripeService->createSetupIntent(customer: $customer);

        $stripeData = $customer->stripe_data;
        $stripeData['setup_intent_id'] = $setupIntent->id;
        $customer->stripe_data = $stripeData;
        $customer->save();

        return $setupIntent;
    }

    public function cancelMandate(Customer $customer): void
    {
        $stripeData = $customer->stripe_data;
        if (
            is_array($stripeData) &&
            (array_key_exists('payment_method', $stripeData) || array_key_exists('mandate_id', $stripeData))
        ) {
            $paymentMethod = $this->stripeService->getPaymentMethod($stripeData['payment_method']);
            if (! $paymentMethod) {
                $mandate = $this->stripeService->getMandate($stripeData['mandate_id']);
                $paymentMethod = $this->stripeService->getPaymentMethod($mandate->payment_method);
            }

            if (! $paymentMethod) {
                throw new Exception(__('Payment method not found'));
            }

            $this->stripeService->detachPaymentMethod($paymentMethod);
            $newStripeData = [
                'stripe_id' => $stripeData['stripe_id'],
            ];

            $customer->stripe_data = $newStripeData;
            $customer->save();
        }
    }
}
