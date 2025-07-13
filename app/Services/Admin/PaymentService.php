<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\Models\Customer;
use App\Services\Payment\Stripe\StripeService;
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
}
