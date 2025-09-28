<?php

declare(strict_types=1);

namespace App\Services\Admin;

use App\DTO\Order\OrderDTO;
use App\Models\Customer;
use App\Services\Payment\Stripe\StripeService;
use Exception;
use RuntimeException;
use Stripe\Mandate;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod;
use Stripe\SetupIntent;

class PaymentService
{
    public function __construct(
        private StripeService $stripeService,
    ) {}

    public function getStripePaymentMethod(string $paymentMethodId): null|PaymentMethod
    {
        return $this->stripeService->getPaymentMethod($paymentMethodId);
    }

    public function getStripePaymentMethods()
    {
        return $this->stripeService->getPaymentMethods();
    }

    public function attachStripePaymentMethod(Customer $customer, string $paymentMethodId)
    {
        $paymentMethod = $this->getStripePaymentMethod($paymentMethodId);
        $paymentMethod?->attach([
            'customer' => $customer->stripe_data['stripe_id'],
        ]);

        $testPaymentMethodResponse = $this->stripeService->createTestPaymentIntent(
            customer: $customer,
            paymentMethodId: $paymentMethodId,
        );

        if ($testPaymentMethodResponse['success']) {
            $stripeData = $customer->stripe_data;
            $stripeData['payment_method'] = $paymentMethodId;
            $stripeData['payment_method_chargable'] = true;
            $stripeData['payment_method_accepted_at'] = now()->timestamp;
            $customer->stripe_data = $stripeData;
            $customer->save();
        } else {
            throw new RuntimeException($testPaymentMethodResponse['message']);
        }
    }

    public function getStripeSetupIntent(string $setupIntentId): SetupIntent
    {
        return $this->stripeService->getSetupIntent(
            setupIntentId: $setupIntentId,
        );
    }

    public function createStripeSetupIntent(Customer $customer): SetupIntent
    {
        if ($customer->stripe_data === null || ! array_key_exists('stripe_id', $customer->stripe_data)) {
            $stripeCustomer = $this->stripeService->getCustomers(
                params: [
                    'email' => $customer->email,
                ],
            )->first();
            if (! $stripeCustomer) {
                $stripeCustomer = $this->stripeService->createCustomer(
                    customer: $customer,
                );
            }
            $stripeData = $customer->stripe_data ?? [];
            $stripeData['stripe_id'] = $stripeCustomer->id;
            $customer->stripe_data = $stripeData;
            $customer->save();
        }
        $setupIntent = $this->stripeService->createSetupIntent(
            customer: $customer,
        );

        $stripeData = $customer->stripe_data;
        $stripeData['setup_intent_id'] = $setupIntent->id;
        $customer->stripe_data = $stripeData;
        $customer->save();

        return $setupIntent;
    }

    public function createStripePaymentIntent(OrderDTO $orderDTO, Customer $customer): PaymentIntent
    {
        return $this->stripeService->createPaymentIntent(
            orderDTO: $orderDTO,
            customer: $customer,
        );
    }

    public function getStripeMandate(string $mandateId): Mandate
    {
        return $this->stripeService->getMandate(
            mandateId: $mandateId,
        );
    }

    public function cancelStripeMandate(Customer $customer): void
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
